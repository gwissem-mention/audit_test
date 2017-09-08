<?php

namespace HopitalNumerique\NewAccountBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use HopitalNumerique\NewAccountBundle\DependencyInjection\Compiler\AccountDashboardWidgetsCompilerPass;

class NewAccountBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AccountDashboardWidgetsCompilerPass());
    }
}
