<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Exceptions;

class ValidationException extends \Exception
{
    /**
     * @param array<string> $errors
     */
    public function __construct(
        string $entityName,
        array $errors,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                '"%s" entity is invalid: %s',
                $entityName,
                implode(', ', $errors)
            ),
            $code,
            $previous
        );
    }
}
