<?php

namespace HopitalNumerique\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class NotificationCompilerPass.
 */
class NotificationCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('notification.aggregator')) {
            return;
        }

        $definition = $container->findDefinition('notification.aggregator');

        $taggedServices = $container->findTaggedServiceIds('notification.notification_aggregator');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                $definition->addMethodCall('addProvider', [
                    new Reference($id),
                    $tag['key'],
                ]);
            }
        }
    }
}
