<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('agentchk');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        if (!$rootNode instanceof ArrayNodeDefinition) {
            throw new \RuntimeException('Root node must be an instance of ArrayNodeDefinition');
        }

        $rootNode
            ->children()
                ->arrayNode('profiles')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('tools')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('tools')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('command')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('args')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
