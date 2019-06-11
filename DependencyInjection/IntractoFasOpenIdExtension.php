<?php

namespace Intracto\FasOpenIdBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class IntractoFasOpenIdExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $oauthClientDefinition = $container->getDefinition('intracto.fas_open_id.oauth_client');
        $oauthClientDefinition->setArgument(0, $config['client_id']);
        $oauthClientDefinition->setArgument(1, $config['client_secret']);
        $oauthClientDefinition->setArgument(2, $config['scope']);
        $oauthClientDefinition->setArgument(3, $config['auth_path']);

        $authenicatorDefinition = $container->getDefinition('intracto.fas_open_id.authenticator');
        $authenicatorDefinition->setArgument(0, $config['auth_path']);
        $authenicatorDefinition->setArgument(1, $config['target_path']);
        $authenicatorDefinition->setArgument(2, $config['scope']);
    }
}
