<?php

namespace NicolasJoubert\GrabitBundle\Validator;

use NicolasJoubert\GrabitBundle\Grabber\Exceptions\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    public function __construct(private readonly ValidatorInterface $validator) {}

    /**
     * @throws ValidationException
     */
    public function validate(mixed $object): void
    {
        $violations = $this->validator->validate($object);
        if (0 !== count($violations)) {
            // there are errors, now you can show them
            $errors = [];
            foreach ($violations as $violation) {
                /** @var null|bool|float|int|string $invalidValue */
                $invalidValue = $violation->getInvalidValue();
                $errors[] = sprintf(
                    '"%s" => %s (%s)',
                    $violation->getPropertyPath(),
                    $violation->getMessage(),
                    $invalidValue
                );
            }

            // @phpstan-ignore argument.type
            throw new ValidationException((new \ReflectionClass($object))->getShortName(), $errors);
        }
    }
}
