<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Event;

use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base class for events carrying a Source and the error which triggered them.
 */
abstract class AbstractSourceEvent extends Event
{
    public function __construct(
        private readonly SourceInterface $source,
        private readonly string $reason,
    ) {}

    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    /**
     * Message of the error which triggered this event.
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
