<?php

namespace NicolasJoubert\GrabitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-import-type Configuration from \NicolasJoubert\GrabitBundle\Model\TemplateInterface as TemplateConfiguration
 *
 * @phpstan-type GrabitConfiguration array{
 *     'class': array{'extracted_data': string, 'source': string, 'template': string, 'grabbed': string},
 *     'proxy_urls': array{'flaresolverr': string, 'squid': string},
 *     'templates': array<string, TemplateConfiguration>
 * }
 */
class GrabitExtension extends ConfigurableExtension
{
    /**
     * @param array<string, array<string, mixed>> $mergedConfig
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__).'/../config'));
        $loader->load('services.yaml');

        // Templates
        /** @var array{'grabit': array{'templates': array<string, TemplateConfiguration>}} $defaultTemplates */
        $defaultTemplates = Yaml::parse(
            // @phpstan-ignore argument.type
            file_get_contents(\dirname(__DIR__).'/../config/default_templates.yaml')
        );

        $mergedConfig['templates'] = array_merge(
            $defaultTemplates['grabit']['templates'],
            $mergedConfig['templates'] ?? [],
        );

        $configuration = new Configuration();

        /** @var GrabitConfiguration $config */
        $config = $this->processConfiguration($configuration, ['grabit' => $mergedConfig]);

        $definition = $container->findDefinition('grabit.grabber.template');
        $definition->replaceArgument(1, $config['templates']);

        // Proxies
        $definition = $container->findDefinition('grabit.grabber.client.proxy_flaresolverr');
        $definition->replaceArgument(1, $config['proxy_urls']['flaresolverr']);
        $definition = $container->findDefinition('grabit.grabber.client.proxy_squid');
        $definition->replaceArgument(1, $config['proxy_urls']['squid']);

        // Models
        $container->setParameter('grabit.model.extracted_data.class', $config['class']['extracted_data']);
        $container->setParameter('grabit.model.source.class', $config['class']['source']);
        $container->setParameter('grabit.model.template.class', $config['class']['template']);
        $container->setParameter('grabit.dto.grabbed.class', $config['class']['grabbed']);
    }
}
