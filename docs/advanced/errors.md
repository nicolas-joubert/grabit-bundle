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

## Events

Two events are dispatched by the `grabit:grab` command so you can react to failures
(send a mail, ping a webhook, log to a dedicated channel, …):

| Event                                                          | When it is dispatched                                                                          |
|----------------------------------------------------------------|------------------------------------------------------------------------------------------------|
| `NicolasJoubert\GrabitBundle\Event\SourceErrorEvent`           | Each time an error is recorded on a source, right after `countError`/`lastError` are persisted. |
| `NicolasJoubert\GrabitBundle\Event\SourceDisabledEvent`        | Only when that error made the source cross its `maxNumberError` threshold and be disabled.     |

A run which disables a source therefore dispatches **both** events, `SourceErrorEvent`
first. `SourceDisabledEvent` is dispatched on the transition only: re-running an
already-disabled source (with `--source_id`) still dispatches `SourceErrorEvent`, but not
`SourceDisabledEvent`.

Both events extend `AbstractSourceEvent` and expose:

| Method        | Description                                                            |
|---------------|------------------------------------------------------------------------|
| `getSource()` | The `SourceInterface` being processed, already updated and flushed.    |
| `getReason()` | Message of the error which triggered the event (same as `lastError`).  |

```php
// src/EventListener/GrabitErrorListener.php

namespace App\EventListener;

use NicolasJoubert\GrabitBundle\Event\SourceDisabledEvent;
use NicolasJoubert\GrabitBundle\Event\SourceErrorEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class GrabitErrorListener
{
    public function __construct(private LoggerInterface $logger) {}

    #[AsEventListener]
    public function onError(SourceErrorEvent $event): void
    {
        $this->logger->warning('Grab failed for source "{label}": {reason}', [
            'label' => $event->getSource()->getLabel(),
            'reason' => $event->getReason(),
        ]);
    }

    #[AsEventListener]
    public function onDisabled(SourceDisabledEvent $event): void
    {
        $source = $event->getSource();

        $this->logger->error('Source "{label}" has been disabled after {count} errors', [
            'label' => $source->getLabel(),
            'count' => $source->getCountError(),
        ]);
    }
}
```

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
