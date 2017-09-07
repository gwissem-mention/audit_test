<?php

namespace HopitalNumerique\CartBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ReportGeneratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('hopitalnumerique_cart.report_generator')) {
            return;
        }

        $definition = $container->findDefinition(
            'hopitalnumerique_cart.report_generator'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'cart_bundle.report_generator'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addGenerator',
                [new Reference($id)]
            );
        }
    }
}
