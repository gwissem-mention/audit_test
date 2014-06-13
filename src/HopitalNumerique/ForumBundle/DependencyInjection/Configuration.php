<?php

namespace HopitalNumerique\ForumBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use CCDNForum\ForumBundle\DependencyInjection\Configuration as ConfigurationCCDN;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 */
class Configuration extends ConfigurationCCDN
{
    /**
     *
     * @access private
     * @param  \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     * @return \HopitalNumerique\ForumBundle\DependencyInjection\Configuration
     */
    private function addEntitySection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('entity')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('forum')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Forum')->end()
                            ->end()
                        ->end()
                        ->arrayNode('category')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Category')->end()
                            ->end()
                        ->end()
                        ->arrayNode('board')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Board')->end()
                            ->end()
                        ->end()
                        ->arrayNode('topic')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Topic')->end()
                            ->end()
                        ->end()
                        ->arrayNode('post')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Post')->end()
                            ->end()
                        ->end()
                        ->arrayNode('subscription')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Subscription')->end()
                            ->end()
                        ->end()
                        ->arrayNode('registry')
                            ->addDefaultsIfNotSet()
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('class')->defaultValue('HopitalNumerique\ForumBundle\Entity\Registry')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }
}