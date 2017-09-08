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
        if (!$container->has('HopitalNumerique\NotificationBundle\Service\Notifications')) {
            return;
        }

        $definition = $container->findDefinition('HopitalNumerique\NotificationBundle\Service\Notifications');

        $taggedServices = $container->findTaggedServiceIds('notification.aggregator');

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
