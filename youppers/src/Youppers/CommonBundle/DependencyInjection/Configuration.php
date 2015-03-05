<?php

namespace Youppers\CommonBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('youppers_common');
        
        $this->addQrSection($rootNode);
        
        return $treeBuilder;
    }
    
    private function addQrSection(ArrayNodeDefinition $rootNode)
    {
    	$rootNode
    		->children()
    			->arrayNode('qr')->isRequired()->requiresAtLeastOneElement()
    				->useAttributeAsKey('type')
    				->prototype('array')
    					->children()
						    ->scalarNode('entity')->isRequired()->end()
						    ->scalarNode('route')->isRequired()->end()
						    ->end()
    					->end()
    				->end()
    			->end()
    		->end();
    }
}
