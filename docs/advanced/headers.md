# Source Headers

Each request sent by Grabit ships with a set of default HTTP headers (a realistic
`User-Agent`, `Accept`, `Accept-Language`, `Cache-Control`, …) so that the targeted
website answers as it would to a regular browser. These defaults live in
`BaseClient::getBaseHeaders()`.

You can override or extend them **per source**. This is useful when a website
requires a specific cookie, an API key, an authorization token, or a custom
`User-Agent`.

## How headers are stored

Headers are stored on the `Source` entity as a list of `type` / `content` pairs:

```php
$source->setHeaders([
    ['type' => 'Authorization', 'content' => 'Bearer my-secret-token'],
    ['type' => 'Cookie', 'content' => 'sessionid=abcdef; consent=1'],
    ['type' => 'User-Agent', 'content' => 'MyCustomBot/1.0'],
]);
```

In SQL the same value is stored as JSON in the `grabit_source.headers` column:

```sql
UPDATE app.grabit_source
SET headers = '[{"type":"Authorization","content":"Bearer my-secret-token"}]'
WHERE id = 1;
```

## How headers are merged

At request time, `Source::formatHeaders()` merges your source headers on top of the
default headers and returns a list of `Header: value` strings ready for the HTTP
client:

1. The default headers are used as a base.
2. Each source header is added; **a source header overrides a default header that
   has the same `type`**.
3. The result is flattened to the `"<type>: <content>"` format expected by the
   Symfony HTTP client.

> **Note**
> Header names are case-sensitive when overriding. To replace the default
> `User-Agent`, use exactly `User-Agent` as the `type`.

## Proxy specifics

When the [FlareSolverr proxy](proxies/flaresolverr.md) is used, the client forces
`Content-Type: application/json` on top of the merged headers, because the request
to FlareSolverr is itself a JSON payload. Your custom headers are still forwarded to
the targeted website by FlareSolverr.
