<?php

namespace NicolasJoubert\GrabitBundle\DependencyInjection;

use NicolasJoubert\GrabitBundle\Dto\Grabbed;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('grabit');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->addClassSection($rootNode);
        $this->addProxySection($rootNode);
        $this->addTemplateSection($rootNode);

        return $treeBuilder;
    }

    protected function addClassSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('class')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('extracted_data')->defaultValue('App\Entity\ExtractedData')->end()
                        ->scalarNode('source')->defaultValue('App\Entity\Source')->end()
                        ->scalarNode('template')->defaultValue('App\Entity\Template')->end()
                        ->scalarNode('grabbed')->defaultValue(Grabbed::class)->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    protected function addProxySection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('proxy_urls')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('flaresolverr')->defaultValue('')->end()
                        ->scalarNode('squid')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    protected function addTemplateSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('templates')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('container')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('contents')
                                ->children()
                                    ->append($this->addContentNode('unique', true))
                                    ->append($this->addContentNode('title', true))
                                    ->append($this->addContentNode('description', true))
                                    ->append($this->addContentNode('link', true))
                                    ->append($this->addContentNode('publicationDate', false))
                                    ->append($this->addContentNode('image', false))
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    protected function addContentNode(string $name, bool $required): NodeDefinition
    {
        $treeBuilder = new TreeBuilder($name);

        $node = $treeBuilder->getRootNode()
            ->beforeNormalization()
                ->ifString()
                ->then(fn (string $v): array => ['filter' => $v])
            ->end()
            ->children()
                ->scalarNode('type')->end()
                ->scalarNode('filter')->end()
                ->scalarNode('extract')->end()
                ->scalarNode('content')->end()
                ->scalarNode('clean')->end()
                ->scalarNode('json')->end()
                ->arrayNode('concat')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(fn (string $v): array => ['filter' => $v])
                        ->end()
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('filter')->end()
                            ->scalarNode('extract')->end()
                            ->scalarNode('content')->end()
                            ->scalarNode('clean')->end()
                            ->scalarNode('json')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        if ($required) {
            $node->isRequired();
        }

        return $node;
    }
}
