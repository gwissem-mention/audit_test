<?php

namespace HopitalNumerique\AutodiagBundle;

use HopitalNumerique\AutodiagBundle\DependencyInjection\Compiler\AttributeBuilderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HopitalNumeriqueAutodiagBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AttributeBuilderCompilerPass());
    }
}
