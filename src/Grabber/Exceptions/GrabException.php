<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Exceptions;

use NicolasJoubert\GrabitBundle\Model\SourceInterface;

class GrabException extends \Exception
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        SourceInterface $source,
        array $parameters = [],
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Error while grabbing Source #%s. %s',
                $source->getId(),
                print_r(array_merge(['Error' => $message], $parameters), true)
            ),
            $code,
            $previous
        );
    }
}
