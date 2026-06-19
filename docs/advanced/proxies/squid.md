# Squid Proxy

[Squid](http://www.squid-cache.org/) is a forward HTTP proxy. Routing a source
through Squid lets you change the originating IP address of the request, which is
handy to bypass IP-based rate limiting or geographic restrictions.

## When to use it

- The target throttles or blocks your server IP.
- You need to grab from a specific region (through a geo-located proxy).
- You want to centralize and cache outgoing requests.
- You want to **strip the proxy-revealing and tracking headers** that anti-bot
  solutions use to flag automated traffic (see
  [Avoiding anti-bot detection](#avoiding-anti-bot-detection)).

For websites protected by Cloudflare / JavaScript challenges, use the
[FlareSolverr proxy](flaresolverr.md) instead.

## Configuration

Set the Squid endpoint in the bundle configuration:

```yaml
# config/packages/grabit.yaml

grabit:
  proxy_urls:
    squid: 'http://user:password@my-squid-host:3128'
```

It is recommended to keep the credentials outside of the file, using an environment
variable:

```yaml
# config/packages/grabit.yaml

grabit:
  proxy_urls:
    squid: '%env(GRABIT_SQUID_PROXY_URL)%'
```

```dotenv
# .env.local
GRABIT_SQUID_PROXY_URL=http://user:password@my-squid-host:3128
```

> **Note**
> The Squid client disables TLS peer/host verification (`verify_peer` and
> `verify_host` are set to `false`) so that requests work through proxies that
> terminate or re-sign TLS.

## Avoiding anti-bot detection

By default, a forward proxy *adds* headers (`Via`, `X-Forwarded-For`, …) and serves
branded error pages that immediately reveal an intermediary. Anti-bot solutions look
for exactly these signals. A hardened Squid can instead make the request look like it
comes straight from a regular browser, by **deleting the proxy-revealing headers** and
**allowing only a browser-like whitelist** of request headers through.

The complete configuration below is a working reference (Squid 6/7):

```squid
# squid.conf

# --- Intercept HTTPS so headers can be rewritten on TLS traffic ---
# Without ssl-bump, HTTPS requests are opaque CONNECT tunnels and headers
# cannot be filtered. A self-signed CA is generated to re-sign traffic.
http_port 3128 ssl-bump tls-cert=/etc/squid/ssl_cert/squidCA.pem tls-key=/etc/squid/ssl_cert/squidCA.pem generate-host-certificates=on
sslcrtd_program /usr/lib/squid/security_file_certgen -s /var/lib/ssl_db -M 4MB
ssl_bump server-first all
ssl_bump bump all

# --- Access control + authentication ---
# Without an explicit "http_access allow", Squid denies every request (implicit
# "deny all"), so these rules are required for the proxy to forward anything.
acl safe_ports port 80 443
http_access deny !safe_ports
acl safe_methods method GET POST OPTIONS CONNECT
http_access deny !safe_methods
# Basic auth credentials are provisioned in /etc/squid/passwd (see entrypoint.sh below)
auth_param basic program /usr/lib/squid/basic_ncsa_auth /etc/squid/passwd
auth_param basic children 5
auth_param basic realm proxy
acl auth proxy_auth REQUIRED
http_access allow auth

# --- Hide the proxy itself ---
follow_x_forwarded_for deny all
httpd_suppress_version_string on   # no Squid version in error pages
via off                            # drop the "Via" header
forwarded_for delete               # drop the "X-Forwarded-For" header

# --- Remove branded error pages (a 400 returns a bare TCP reset) ---
acl errors http_status 400
deny_info TCP_RESET errors
http_reply_access deny errors

# --- Allow ONLY a browser-like set of request headers, deny the rest ---
request_header_access Authorization allow all
request_header_access Cache-Control allow all
request_header_access Content-Length allow all
request_header_access Content-Type allow all
request_header_access Date allow all
request_header_access Host allow all
request_header_access If-Modified-Since allow all
request_header_access Pragma allow all
request_header_access Accept allow all
request_header_access Accept-Charset allow all
request_header_access Accept-Encoding allow all
request_header_access Accept-Language allow all
request_header_access Connection allow all
# Headers sent by Grabit's default client
request_header_access Sec-Fetch-Dest allow all
request_header_access Sec-Fetch-Mode allow all
request_header_access Sec-Fetch-Site allow all
request_header_access Upgrade-Insecure-Requests allow all
request_header_access User-Agent allow all
request_header_access Cookie allow all
# Strip everything else (tracking / proxy-revealing headers)
request_header_access All deny all

# --- Reduce footprint: no caching, no verbose logging ---
cache_mem 8 MB
cache_log /dev/null
```

> **Important — keep the whitelist in sync with your source headers**
> The final `request_header_access All deny all` removes **any** header not explicitly
> allowed above. The whitelist already covers every header from Grabit's default client
> (`Accept`, `Accept-Language`, `Cache-Control`, `Connection`, `Pragma`, `Sec-Fetch-*`,
> `Upgrade-Insecure-Requests`, `User-Agent`) plus `Cookie` and `Authorization`. If you
> add a **custom** header on a source (see [Headers](../headers.md)) that is not in this
> list, Squid will silently drop it before it reaches the target. Add a matching
> `request_header_access <Header> allow all` line for any extra header you rely on.

> **Note on HTTPS interception**
> Because Squid re-signs HTTPS traffic with its own self-signed CA, the client must not
> verify the certificate. Grabit's Squid client already disables TLS verification
> (`verify_peer` / `verify_host` set to `false`), so no extra configuration is needed on
> the application side.

You can package this configuration into a Docker image (self-signed CA generation,
HTTP basic auth) so the hardened proxy ships as a single container next to your
application.

### Example Docker image

The layout below builds a self-contained hardened Squid. Place the `squid.conf` from
the previous section in `conf/squid.conf`:

```
squid/
├── Dockerfile
├── entrypoint.sh
└── conf/
    └── squid.conf
```

```dockerfile
# Dockerfile
FROM alpine:latest

# squid: the proxy — openssl: generate the ssl-bump CA
# ca-certificates: validate upstream HTTPS — apache2-utils: htpasswd for basic auth
RUN apk update && apk add \
    squid \
    openssl \
    ca-certificates \
    apache2-utils

# ssl-bump requirements: generate the self-signed CA used to re-sign HTTPS traffic
RUN mkdir -p /var/spool/squid /etc/squid/ssl_cert \
    && openssl req -subj '/CN=localhost' -x509 -newkey rsa:4096 -nodes \
        -keyout /etc/squid/ssl_cert/squidCA.pem -out /etc/squid/ssl_cert/squidCA.pem -days 365 \
    && rm -rf /var/lib/ssl_db \
    && /usr/lib/squid/security_file_certgen -c -s /var/lib/ssl_db -M 4MB \
    && chown -R squid:squid /var/lib/ssl_db /var/spool/squid /etc/squid/ssl_cert

COPY conf/squid.conf /etc/squid/squid.conf

EXPOSE 3128/tcp

COPY entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]
```

```sh
# entrypoint.sh — provision basic-auth credentials, then run Squid in the foreground
#!/bin/sh
set -e

if [ -z "${USERNAME}" ] || [ -z "${PASSWORD}" ]; then
  echo "You must provide -e USERNAME=... and -e PASSWORD=..."
  exit 1
fi

htpasswd -cb /etc/squid/passwd "${USERNAME}" "${PASSWORD}"

exec "$(which squid)" -NYCd 1
```

Build and run it, passing the basic-auth credentials expected by the `squid.conf`
above:

```bash
docker build -t grabit-squid ./squid
docker run -d --name grabit-squid -p 3128:3128 \
  -e USERNAME=grabit \
  -e PASSWORD=a-strong-password \
  grabit-squid
```

Then point the bundle at it:

```dotenv
# .env.local
GRABIT_SQUID_PROXY_URL=http://grabit:a-strong-password@127.0.0.1:3128
```

## Enabling Squid on a source

Set the source `proxy` to `squid`:

```sql
UPDATE app.grabit_source
SET proxy = 'squid'
WHERE id = 1;
```

```php
use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;

$source->setProxy(SourceProxy::SQUID);
```

When the source runs, the request is sent through the configured Squid URL. If
`grabit.proxy_urls.squid` is empty, the command fails with:

```
Cannot use ProxySquidClient without defining grabit.proxy_urls.squid var
```
