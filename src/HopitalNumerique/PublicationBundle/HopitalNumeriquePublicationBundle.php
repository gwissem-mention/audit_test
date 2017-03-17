<?php

namespace HopitalNumerique\PublicationBundle;

use HopitalNumerique\PublicationBundle\DependencyInjection\Compiler\RelationFinderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HopitalNumeriquePublicationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RelationFinderPass());
    }
}
