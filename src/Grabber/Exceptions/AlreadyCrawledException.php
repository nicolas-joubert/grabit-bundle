<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Exceptions;

use NicolasJoubert\GrabitBundle\Dto\GrabbedInterface;

class AlreadyCrawledException extends \Exception
{
    /**
     * @param GrabbedInterface[] $grabbeds
     */
    public function __construct(private readonly array $grabbeds)
    {
        parent::__construct('', 200, null);
    }

    /**
     * @return GrabbedInterface[]
     */
    public function getGrabbeds(): array
    {
        return $this->grabbeds;
    }
}
