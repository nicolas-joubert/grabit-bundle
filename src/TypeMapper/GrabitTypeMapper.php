<?php

namespace NicolasJoubert\GrabitBundle\TypeMapper;

use Dunglas\DoctrineJsonOdm\TypeMapper;
use Dunglas\DoctrineJsonOdm\TypeMapperInterface;

class GrabitTypeMapper implements TypeMapperInterface
{
    /**
     * @param class-string $grabbedClassName
     */
    public function __construct(private readonly ?TypeMapper $decorated, private readonly string $grabbedClassName) {}

    public function getTypeByClass(string $class): string
    {
        return $class === $this->grabbedClassName ? 'grabbed' : ($this->decorated?->getTypeByClass($class) ?? $class);
    }

    /**
     * @return class-string|string
     */
    public function getClassByType(string $type): string
    {
        return 'grabbed' === $type ? $this->grabbedClassName : ($this->decorated?->getClassByType($type) ?? $type);
    }
}
