<?php

namespace HopitalNumerique\NewAccountBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AccountDashboardWidgetsCompilerPass
 */
class AccountDashboardWidgetsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('new_account.dashboard.widgets_aggregator')) {
            return;
        }

        $definition = $container->findDefinition(
            'new_account.dashboard.widgets_aggregator'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'new_account.dashboard_widget'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                $arguments = [
                    new Reference($id),
                    $tag['type'],
                ];

                if (isset($tag['priority'])) {
                    $arguments[] = $tag['priority'];
                }

                $definition->addMethodCall('addWidget', $arguments);
            }
        }
    }
}
