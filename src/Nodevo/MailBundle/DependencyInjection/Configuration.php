<?php

namespace Nodevo\MailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nodevo_mail');

        $rootNode
        	->children()
        	    ->arrayNode('autorisations')
        	    	->children()        	    	
        				->booleanNode('allowAdd')->defaultTrue()->end()
        				->booleanNode('allowDelete')->defaultTrue()->end()
        			->end()
        		->end()
        		->arrayNode('expediteur')
        	    	->children()	    	
        				->scalarNode('mail')->isRequired()->cannotBeEmpty()->end()
        				->scalarNode('nom')->isRequired()->cannotBeEmpty()->end()
        			->end()
        		->end()
        		->arrayNode('test')
                    ->addDefaultsIfNotSet()
        	    	->children()	    	
        				->scalarNode('destinataire')->isRequired()->cannotBeEmpty()->end()
        			->end()
        		->end()
        	->end()
        ;

        return $treeBuilder;
    }
}
