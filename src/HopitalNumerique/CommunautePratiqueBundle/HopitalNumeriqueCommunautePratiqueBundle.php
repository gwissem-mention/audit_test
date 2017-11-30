<?php

namespace HopitalNumerique\CommunautePratiqueBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Compiler\WallItemRetrieverPass;

class HopitalNumeriqueCommunautePratiqueBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new WallItemRetrieverPass());
    }
}
