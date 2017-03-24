<?php

namespace HopitalNumerique\PublicationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RelationFinderPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('hopitalnumerique_publication.service.relation_finder')) {
            return;
        }

        $definition = $container->findDefinition('hopitalnumerique_publication.service.relation_finder');

        $taggedServices = $container->findTaggedServiceIds('resource.relation_finder');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addFinder', [new Reference($id)]);
        }
    }
}
