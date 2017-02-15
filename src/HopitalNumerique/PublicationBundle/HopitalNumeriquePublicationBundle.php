<?php

namespace HopitalNumerique\PublicationBundle;

use HopitalNumerique\PublicationBundle\DependencyInjection\Compiler\RelationFinderPass;
use HopitalNumerique\PublicationBundle\DependencyInjection\Compiler\ConverterNodeParserPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HopitalNumeriquePublicationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RelationFinderPass());
        $container->addCompilerPass(new ConverterNodeParserPass());
    }
}
