<?php

namespace Nodevo\ToolsBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;
use Nodevo\ToolsBundle\Generator\CrudGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class CrudCommand extends GenerateDoctrineCrudCommand
{
    protected $generator;

    protected function configure()
    {
        parent::configure();

        $this->setName('nodevo:generate:crud');
        $this->setDescription('Création du crud nodevo');
    }

    /**
     * Ajoute le chemin du skeleton de notre ToolsBundle.
     */
    protected function getGenerator(BundleInterface $bundle = null)
    {
        if (null === $this->generator) {
            $path = $this->getContainer()->get('kernel')->locateResource('@NodevoToolsBundle/Resources/skeleton/');

            $this->generator = $this->createGenerator();
            $skeletonDirs = array_merge([$path], $this->getSkeletonDirs($bundle));

            $this->generator->setSkeletonDirs($skeletonDirs);
        }

        return $this->generator;
    }

    /**
     * Exécute le form générator pendant la création du crud.
     */
    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $path = $this->getContainer()->get('kernel')->locateResource('@NodevoToolsBundle/Resources/skeleton/');

            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));

            $skeletonDirs = array_merge([$path], $this->getSkeletonDirs($bundle));
            $this->formGenerator->setSkeletonDirs($skeletonDirs);
        }

        return $this->formGenerator;
    }

    /**
     * Créer un generator de type Nodevo.
     *
     * @return CrudGenerator
     */
    protected function createGenerator()
    {
        return new CrudGenerator($this->getContainer()->get('filesystem'));
    }
}
