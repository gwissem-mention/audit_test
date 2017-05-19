<?php

namespace HopitalNumerique\PublicationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConverterNodeParserPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('hopitalnumerique_publication.node.parser')) {
            return;
        }

        $definition = $container->findDefinition('hopitalnumerique_publication.node.parser');

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('converter.node_parser');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $definition->addMethodCall('addParser', array(new Reference($id), $priority));
            }
        }
    }
}
