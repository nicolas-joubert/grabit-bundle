<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Event;

/**
 * Dispatched by the grab command each time an error is recorded on a Source,
 * after `countError` and `lastError` have been persisted.
 */
final class SourceErrorEvent extends AbstractSourceEvent {}
