<?php
 
namespace Nodevo\ToolsBundle\Command;
 
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;

use Nodevo\ToolsBundle\Generator\BundleGenerator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleCommand extends GenerateBundleCommand
{
    protected $generator;
 
    /**
     * Création de la Commande
     */
    protected function configure()
    {
        parent::configure();
 
        $this->setName('nodevo:generate:bundle');
        $this->setDescription('Création du bundle de type Nodevo');
    }

    /**
     * Ajoute le chemin du skeleton de notre ToolsBundle
     */
    protected function getGenerator(BundleInterface $bundle = null)
    {
        if (null === $this->generator) {
            $path = $this->getContainer()->get('kernel')->locateResource('@NodevoToolsBundle/Resources/skeleton/');
            
            $this->generator = $this->createGenerator();
            $skeletonDirs    = array_merge( array($path), $this->getSkeletonDirs($bundle) );

            $this->generator->setSkeletonDirs( $skeletonDirs );
        }

        return $this->generator;
    }

    /**
     * Créer un generator de type Nodevo
     *
     * @return BundleGenerator
     */
    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }
}