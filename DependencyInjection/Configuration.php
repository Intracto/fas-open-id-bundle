<?php

namespace Intracto\FasOpenIdBundle\DependencyInjection;

use Intracto\FasOpenIdBundle\Security\User\User;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('intracto_fas_open_id');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('base_uri')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->info('Define base authentication uri')
                ->end()
                ->scalarNode('client_id')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->info('Client ID of your integration')
                ->end()
                ->scalarNode('client_secret')
                    ->isRequired()
                    ->info('Client Secret of your integration')
                ->end()
                ->arrayNode('scope')
                    ->info('Define the scopes you want to use')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('auth_path')
                    ->defaultValue('intracto_fas_open_id.auth')
                    ->info('The path where the code will be catched')
                ->end()
                ->scalarNode('target_path')
                    ->isRequired()
                    ->info('The path where user will be redirected after login')
                ->end()
                ->scalarNode('login_path')
                    ->isRequired()
                    ->info('The path where user will be redirected when he has to log in')
                ->end()
                ->scalarNode('user_class')
                    ->defaultValue(User::class)
                    ->info('If you extend the user class of this bundle, provide the FQN of your user class')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
