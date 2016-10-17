<?php
namespace HopitalNumerique\AutodiagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AttributeBuilderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('autodiag.attribute_builder_provider')) {
            return;
        }

        $definition = $container->findDefinition(
            'autodiag.attribute_builder_provider'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'autodiag.attribute_builder'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addBuilder',
                array(new Reference($id))
            );
        }
    }
}
