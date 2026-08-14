<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Event;

/**
 * Dispatched by the grab command when a Source is disabled because its
 * `countError` exceeded its `maxNumberError`.
 */
final class SourceDisabledEvent extends AbstractSourceEvent {}
