# Source Templates

A **template** describes *how* to extract structured items from a page. Every source
references a template by its code; the template tells Grabit which repeating block to
loop over (the *container*) and how to build each field of the `Grabbed` DTO from that
block.

Templates can be declared in two ways:

- **In configuration** — under `grabit.templates` in `config/packages/grabit.yaml`.
  The bundle also ships ready-to-use templates (see
  [`config/default_templates.yaml`](../../config/default_templates.yaml)): `symfony_blog`,
  `symfony_ux_blog`, `sylius_blog`, `ticketmaster_api`, `francebillet`, `php` and
  `github_release`.
- **In database** — through the `Template` entity, which stores the same structure as
  JSON in `grabit_template.configuration`.

Configured templates and default templates are merged at compile time (your templates
override the defaults when codes collide).

## Anatomy of a template

```yaml
# config/packages/grabit.yaml

grabit:
  templates:
    my_template:                 # template code, referenced by Source.template
      content_replace:           # optional: raw string replacements applied before crawling
        '<br>': "\n"
      container: 'channel item'  # CSS selector of the repeating block (required)
      contents:                  # how to build each field of the Grabbed DTO
        unique: 'guid'
        title: 'title'
        description: 'description'
        link:
          type: 'text'
          filter: 'link'
        publicationDate: 'pubDate'
        image: 'img'
```

### `container` (required)

A CSS selector matching the repeating element. Grabit loops over every matched node
and produces one item per node. If nothing matches and the page is not empty, a
`No content found` error is raised (see [Errors](errors.md)).

### `content_replace` (optional)

A map of `search: replace` pairs applied to the **raw response body** before it is
parsed. Useful to fix malformed markup or strip wrappers that would break the crawler.

### `contents`

A map of `Grabbed` field name → extraction rule. The default DTO
(`NicolasJoubert\GrabitBundle\Dto\Grabbed`) exposes these fields:

| Field             | Required | Notes                                           |
|-------------------|----------|-------------------------------------------------|
| `unique`          | yes      | Stable id used for de-duplication.              |
| `title`           | yes      |                                                 |
| `description`     | yes      |                                                 |
| `link`            | yes      | Resolved to an absolute URL when `type: link`.  |
| `publicationDate` | no       | Parsed as a date/time.                          |
| `image`           | no       | Resolved to an absolute URL when `type: image`. |

A rule can be a **simple string** (shorthand for `filter`) or a **map** of options.
These two declarations are equivalent:

```yaml
title: 'h2'
# is the same as
title:
  filter: 'h2'
```

## Content rule options

Each entry in `contents` (and each `concat` / `fallback` entry) accepts the following
keys:

| Key        | Description                                                                                                          |
|------------|----------------------------------------------------------------------------------------------------------------------|
| `filter`   | CSS selector applied **inside** the container node to locate the value.                                              |
| `type`     | `text` (default), `link`, `image`, `current_url` or `timestamp`.                                                     |
|            | Defaults are inferred from the field name (`link` → `link`, `image` → `image`, otherwise `text`).                    |
| `extract`  | Read an HTML attribute instead of the text content (e.g. `extract: 'href'`, `extract: 'datetime'`, `extract: 'id'`). |
| `content`  | Use a fixed value instead of reading the node. The special value `now` injects the current date/time (ISO 8601).     |
| `clean`    | A substring removed from the extracted value (e.g. strip a `"Read More"` label).                                     |
| `json`     | Treat the extracted value as JSON and walk a dotted path into it (e.g. `json: 'offers.validFrom'`).                  |
| `concat`   | A list of additional rules whose results are appended to this field's value.                                         |
| `fallback` | A rule used **only** when the main extraction returns an empty value.                                                |

### `type` details

- **`text`** — the text content of the node (default).
- **`link`** — resolves `<a href>` to an absolute URL using the page base URL.
- **`image`** — resolves `<img src>` to an absolute URL.
- **`current_url`** — injects the URL currently being grabbed (no selector needed).
- **`timestamp`** — interprets the extracted value as a UNIX timestamp and formats it
  as an ISO 8601 date.

### Examples

Extract an attribute and provide a literal fallback:

```yaml
title:
  filter: 'h2'
  fallback:
    content: '.'   # use "." when the title is empty
```

Build a link from the current URL plus an anchor (from `php` template):

```yaml
link:
  type: 'current_url'
  concat:
    - type: 'text'
      content: '#'
    - type: 'text'
      extract: 'id'
```

Read a value out of an embedded JSON-LD script (from `francebillet` template):

```yaml
publicationDate:
  filter: 'script[type="application/ld+json"]'
  json: 'offers.validFrom'
  fallback:
    content: 'now'
```

## Result format (HTML / JSON / XML)

A source declares the format returned by the target through its `resultFormat`
(`html` by default, or `json` / `xml`). For JSON and XML, Grabit normalizes the
payload into a crawlable XML tree, so the `container` and `filter` selectors are
expressed against that tree (see how the `ticketmaster_api` template targets
`_embedded > events > *`).

## Validating a template

Use the bundle configuration validation to catch obvious mistakes — `container` is
required and cannot be empty, and `unique`, `title`, `description` and `link` are
required content fields:

```bash
bin/console debug:config grabit
```

## Ticketmaster-API

* Create an account on https://developer-acct.ticketmaster.com/user/login to get API key ("Consumer Key" on "My Apps" after login)
* Get venueId using `https://app.ticketmaster.com/discovery/v2/venues.json?locale=fr-fr&keyword=[NAME]&apikey=[API_KEY]`
* Get events using `https://app.ticketmaster.com/discovery/v2/events.json?locale=fr-fr&venueId=[VENUE_ID]&size=200&apikey=[API_KEY]`
* Check item page>totalElements doesn't exceed max size 200. Use `page=[NUMBER]` is needed.
* Create Source with events list as url, check `is Json result` and select model "Ticketmaster - API"
