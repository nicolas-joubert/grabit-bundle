<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle;

use NicolasJoubert\GrabitBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GrabitBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ResolveTargetEntityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }

    #[\Override]
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
