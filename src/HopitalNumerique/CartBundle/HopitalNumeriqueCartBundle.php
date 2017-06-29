<?php

namespace HopitalNumerique\CartBundle;

use HopitalNumerique\CartBundle\DependencyInjection\Compiler\ItemFactoryCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use HopitalNumerique\CartBundle\DependencyInjection\Compiler\ReportGeneratorCompilerPass;

class HopitalNumeriqueCartBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ReportGeneratorCompilerPass());
        $container->addCompilerPass(new ItemFactoryCompilerPass());
    }
}
