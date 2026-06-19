# FlareSolverr Proxy

[FlareSolverr](https://github.com/FlareSolverr/FlareSolverr) is a proxy server that
solves the JavaScript / browser challenges used by anti-bot solutions such as
Cloudflare. It runs a headless browser, passes the challenge, and returns the final
HTML. Route a source through FlareSolverr when the target answers with a
"Just a moment…" / challenge page instead of the real content.

## When to use it

- The target is protected by Cloudflare (or a similar challenge).
- The content is rendered by JavaScript and not present in the raw HTTP response.

If you only need to change your originating IP, the lighter
[Squid proxy](squid.md) is enough.

## Running FlareSolverr

The simplest way is Docker:

```bash
docker run -d \
  --name flaresolverr \
  -p 8191:8191 \
  -e LOG_LEVEL=info \
  --restart unless-stopped \
  ghcr.io/flaresolverr/flaresolverr:latest
```

This exposes the API on `http://localhost:8191/v1`.

## Configuration

Point the bundle to the FlareSolverr `/v1` endpoint:

```yaml
# config/packages/grabit.yaml

grabit:
  proxy_urls:
    flaresolverr: '%env(GRABIT_FLARESOLVERR_PROXY_URL)%'
```

```dotenv
# .env.local
GRABIT_FLARESOLVERR_PROXY_URL=http://localhost:8191/v1
```

## Enabling FlareSolverr on a source

Set the source `proxy` to `flaresolverr`:

```sql
UPDATE app.grabit_source
SET proxy = 'flaresolverr'
WHERE id = 1;
```

```php
use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;

$source->setProxy(SourceProxy::FLARESOLVERR);
```

When the source runs, Grabit sends a `request.get` command to FlareSolverr (using a
shared `grabit` session), and uses the solved HTML response as the content to crawl.

## How JSON and XML are handled

FlareSolverr always returns rendered HTML. When the source result format is JSON or
XML, FlareSolverr wraps the payload in the browser's viewer markup, so Grabit extracts
the real payload before crawling it:

- **JSON** — extracted from the `body > pre` node.
- **XML** — extracted from the `#webkit-xml-viewer-source-xml` node.

This is transparent: configure the source [result format](../templates.md) as usual.

## Troubleshooting

If `grabit.proxy_urls.flaresolverr` is empty, the command fails with:

```
Cannot use ProxyFlaresolverrClient without defining grabit.proxy_urls.flaresolverr var
```

Make sure the FlareSolverr container is reachable from the application and that the URL
includes the `/v1` path.
