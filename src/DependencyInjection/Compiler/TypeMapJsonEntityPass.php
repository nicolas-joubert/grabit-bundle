<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TypeMapJsonEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('dunglas_doctrine_json_odm', [
            'type_map' => [
                'grabbed' => $container->getParameter('grabit.dto.grabbed.class'),
            ],
        ]);
    }
}
