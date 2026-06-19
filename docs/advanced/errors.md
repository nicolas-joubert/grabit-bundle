# Source Errors

When the `grabit:grab` command processes a source, any failure is caught, recorded on
the source itself, and reported on the command output. This lets you keep grabbing
healthy sources while keeping track of the broken ones.

## Error tracking on the Source

Three fields on the `Source` entity drive the error handling:

| Field            | Description                                                                                   |
|------------------|-----------------------------------------------------------------------------------------------|
| `countError`     | Number of **consecutive** failed runs. Reset to `0` after a successful run.                   |
| `maxNumberError` | Maximum number of consecutive failures tolerated before the source is disabled (default `0`). |
| `lastError`      | Message of the last error encountered, stored for debugging.                                  |
| `enabled`        | When `false`, the source is skipped by `grabit:grab` (unless targeted explicitly).            |

### Auto-disabling

On each failure the command increments `countError` and stores the message in
`lastError`. As soon as `countError` becomes **greater than** `maxNumberError`, the
source is automatically disabled (`enabled = false`) and will no longer be picked up
by the next runs.

> **Example**
> With the default `maxNumberError = 0`, a source is disabled after its **first**
> failure. Set `maxNumberError = 3` to tolerate three consecutive failures before
> disabling.

A successful run resets `countError` to `0`, so transient failures do not pile up
across healthy runs.

## Exceptions

The grabbing pipeline raises a small set of dedicated exceptions
(`src/Grabber/Exceptions`):

| Exception                 | When it is thrown                                                                                                               |
|---------------------------|---------------------------------------------------------------------------------------------------------------------------------|
| `GrabException`           | Top-level wrapper raised by `Grabber::grabSource()`. It includes the source id and contextual parameters                        |       
|                           | (current URL, response content, …). This is the exception caught by the command.                                                |
| `CrawlerException`        | A node could not be parsed or a required content was missing. Carries the offending item,                                       |
|                           | the configuration key and the raw HTML to ease debugging. Also raised when **no content at all** is found (`No content found`). |
| `ValidationException`     | The grabbed data (`Grabbed` DTO or the persisted entity) failed Symfony validation.                                             |
|                           | The message lists the invalid fields.                                                                                           |
| `AlreadyCrawledException` | Internal control-flow exception (HTTP code `200`).                                                                              |
|                           | Not an error: it signals that the last already-known item was reached and crawling can                                          |
|                           | stop early (see [`stopOnLastUniqueContentId`](source.md#stop-on-last-unique-content-id)).                                       |

`GrabException` and `ValidationException` are recorded against the source as described
above. `AlreadyCrawledException` is handled internally and never surfaces as an error.

## Inspecting and recovering a failed source

```sql
-- Find disabled sources and their last error
SELECT id, label, count_error, last_error
FROM app.grabit_source
WHERE enabled = 0;
```

Once the underlying problem is fixed, re-enable the source and reset its counter:

```sql
UPDATE app.grabit_source
SET enabled = 1, count_error = 0, last_error = NULL
WHERE id = 1;
```

You can then re-run a single source without touching the others:

```bash
bin/console grabit:grab --source_id=1
```
