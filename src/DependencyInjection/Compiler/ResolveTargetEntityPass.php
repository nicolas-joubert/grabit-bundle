<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\DependencyInjection\Compiler;

use Doctrine\ORM\Events;
use NicolasJoubert\GrabitBundle\Model\ExtractedDataInterface;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveTargetEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->findDefinition('doctrine.orm.listeners.resolve_target_entity')
            ->addMethodCall(
                'addResolveTargetEntity',
                [ExtractedDataInterface::class, $container->getParameter('grabit.model.extracted_data.class'), []],
            )
            ->addMethodCall(
                'addResolveTargetEntity',
                [SourceInterface::class, $container->getParameter('grabit.model.source.class'), []],
            )
            ->addTag('doctrine.event_listener', ['event' => Events::loadClassMetadata])
            ->addTag('doctrine.event_listener', ['event' => Events::onClassMetadataNotFound])
        ;
    }
}
