<?php

namespace Intracto\FasOpenIdBudle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('fas_open_id');
        if (method_exists($treeBuilder, 'getRoodNode')) {
            $root = $treeBuilder->getRootNode();
        } else {
            $root = $treeBuilder->root('fas_open_id');
        }

        $root
            ->children()
                ->scalarNode('client_id')
                    ->info('Client ID of your integration')
                ->end()
                ->scalarNode('client_secret')
                    ->info('Client Secret of your integration')
                ->end()
                ->arrayNode('scopes')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
