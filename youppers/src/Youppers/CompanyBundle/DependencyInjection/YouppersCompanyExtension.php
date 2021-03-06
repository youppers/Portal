<?php

namespace Youppers\CompanyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class YouppersCompanyExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $bundles = $container->getParameter('kernel.bundles');
        
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $yloader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));        
        $yloader->load('services.yml');
        $yloader->load('products.yml');
        
        //$loader->load('orm.xml');
        //$loader->load('form.xml');
       	//$loader->load('serializer/Session.xml');
        //$loader->load('api_controllers.xml');
        //$loader->load('api_form.xml');
        
        if (isset($bundles['SonataAdminBundle'])) {
        	$loader->load('admin.xml');
        }
                
    }
}
