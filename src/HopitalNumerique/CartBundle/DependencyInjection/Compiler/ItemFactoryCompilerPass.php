<?php

namespace HopitalNumerique\CartBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ItemFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('hopitalnumerique_cart.factory.item')) {
            return;
        }

        $definition = $container->findDefinition(
            'hopitalnumerique_cart.factory.item'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'cart_bundle.item_factory'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addFactory',
                [new Reference($id)]
            );
        }
    }
}
