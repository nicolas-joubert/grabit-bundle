# Source

A **source** is a configured target to grab: one or more URLs, the
[template](templates.md) used to extract items from them, and a few options that
control formatting, networking and error handling. Each run of `grabit:grab` loops
over the enabled sources and produces `ExtractedData` rows from them.

Sources are stored in the `grabit_source` table through the `Source` entity. You can
create them in SQL, through a fixture, an admin (see the
[SonataAdmin bridge](https://github.com/nicolas-joubert/grabit-sonata-admin-bundle)),
or programmatically.

## Creating a source

The minimal source needs a label, at least one URL and a template code:

```sql
INSERT INTO app.grabit_source (label, urls, template)
VALUES ('my_source_for_symfony_blog', 'https://feeds.feedburner.com/symfony/blog', 'symfony_blog');
```

```php
use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;
use NicolasJoubert\GrabitBundle\Model\Enum\SourceResultFormat;

$source = new Source();
$source
    ->setLabel('my_source_for_symfony_blog')
    ->setUrls(['https://feeds.feedburner.com/symfony/blog'])
    ->setTemplate('symfony_blog')
    ->setResultFormat(SourceResultFormat::HTML)
    ->setProxy(SourceProxy::NONE)
;

$entityManager->persist($source);
$entityManager->flush();
```

## Fields

| Field                        | Type                      | Default | Description                                                                                      |
|------------------------------|---------------------------|---------|--------------------------------------------------------------------------------------------------|
| `label`                      | string (required)         | `''`    | Human-readable name of the source. Used in command output and as the `__toString()` value.       |
| `urls`                       | array (required)          | `[]`    | One or more URLs to grab, **in order**. See [Multiple URLs](#multiple-urls).                     |
| `template`                   | string (required)         | `''`    | Code of the [template](templates.md) used to extract items.                                      |
| `headers`                    | array \| null             | `null`  | Per-source HTTP headers merged over the defaults. See [Headers](headers.md).                     |
| `resultFormat`               | enum `SourceResultFormat` | `html`  | Format returned by the target: `html`, `json` or `xml`. See [Result format](#result-format).     |
| `proxy`                      | enum `SourceProxy`        | `none`  | Network route: `none`, `squid` or `flaresolverr`. See [Proxy](#proxy).                           |
| `stopOnLastUniqueContentId`  | bool                      | `true`  | De-duplication strategy. See [Stop on last unique content id](#stop-on-last-unique-content-id).  |
| `enabled`                    | bool                      | `true`  | When `false`, the source is skipped by `grabit:grab` unless targeted with `--source_id`.         |
| `maxNumberError`             | int                       | `0`     | Consecutive failures tolerated before auto-disabling. See [Errors](errors.md).                   |
| `countError`                 | int                       | `0`     | Current count of consecutive failures (managed by the command).                                  |
| `lastError`                  | string \| null            | `null`  | Message of the last error encountered (managed by the command).                                  |

`label`, `urls` and `template` are validated as **not blank**; a source missing one of
them fails validation.

## Multiple URLs

`urls` is an ordered list. Grabit grabs each URL in turn and merges the resulting
items, which is convenient for paginated listings:

```php
$source->setUrls([
    'https://example.com/events?page=1',
    'https://example.com/events?page=2',
    'https://example.com/events?page=3',
]);
```

The order matters: it combines with `stopOnLastUniqueContentId` so that Grabit can stop
paginating as soon as it reaches already-known content.

## Result format

`resultFormat` tells Grabit how the target's response is structured:

- **`html`** (default) — the response is crawled as HTML.
- **`json`** — the JSON payload is normalized into a crawlable XML tree before
  extraction. Template selectors are then expressed against that tree (see the
  `ticketmaster_api` default template).
- **`xml`** — the response is crawled as XML.

The template `container` and `filter` selectors must match the chosen format. See
[Templates → Result format](templates.md#result-format-html--json--xml).

## Proxy

`proxy` selects how the request reaches the target:

- **`none`** (default) — direct request from the application server.
- **`squid`** — routed through a [Squid](proxies/squid.md) forward proxy (change the
  originating IP, bypass IP rate limits).
- **`flaresolverr`** — routed through [FlareSolverr](proxies/flaresolverr.md) to solve
  Cloudflare / JavaScript challenges.

The matching proxy URL must be configured under `grabit.proxy_urls`, otherwise the run
fails. See the [Proxies](proxies.md) section.

## Stop on last unique content id

The `stopOnLastUniqueContentId` flag (default `true`) controls de-duplication strategy:

- **`true`** — the crawler stops as soon as it meets the last item already stored for
  this source. This is efficient for chronological feeds where new items appear on top.
- **`false`** — the crawler loads every known unique id for the source and skips
  duplicates one by one. Use this when items are not ordered or can reappear anywhere
  in the page.

The `unique` field defined in the [template](templates.md) is what identifies an item,
so make sure it is stable across runs.

## Running a single source

By default `grabit:grab` processes every **enabled** source. To grab one source only
(handy to test a new source or re-run a disabled one):

```bash
bin/console grabit:grab --source_id=1
```

See [Errors](errors.md) for how failures are recorded and how to re-enable a disabled
source.
