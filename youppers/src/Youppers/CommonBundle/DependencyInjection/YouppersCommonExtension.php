<?php

namespace Youppers\CommonBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class YouppersCommonExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $bundles = $container->getParameter('kernel.bundles');
        //$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $yloader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //$loader->load('services.xml');
        $yloader->load('services.yml');
        
        if (isset($bundles['SonataAdminBundle'])) {
        	//$loader->load('admin.xml');
        }
        
        $this->configureQr($container, $config);
    }
    
    /**
     * 
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function configureQr(ContainerBuilder $container, array $config)
    {
    	$container->setParameter($this->getAlias() . '.qr', $config['qr']);
    }
    
}
