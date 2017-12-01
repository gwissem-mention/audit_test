<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use HopitalNumerique\CommunautePratiqueBundle\Service\News\WallItemRetriever;
use Symfony\Component\DependencyInjection\Reference;

class WallItemRetrieverPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(WallItemRetriever::class)) {
            return;
        }

        $definition = $container->findDefinition(WallItemRetriever::class);

        $taggedServices = $container->findTaggedServiceIds('cdp.wall_item_retriever');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addRetriever', [
                    new Reference($id),
                    $attributes["alias"],
                ]);
            }
        }

    }
}
