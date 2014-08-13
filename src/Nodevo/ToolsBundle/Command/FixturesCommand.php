<?php

namespace Nodevo\ToolsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Nodevo\ToolsBundle\Generator\DataFixturesGenerator;

class FixturesCommand extends GeneratorCommand
{
	protected $generator;

    /**
     * [configure description]
     *
     * @return [type]
     */
	protected function configure()
	{
		$this
			->setDefinition(array(
					new InputOption('bundle', '', InputOption::VALUE_REQUIRED, 'The bundle in which we generate fixtures'),
                    new InputOption('erase', '', InputOption::VALUE_REQUIRED, 'Should we override existing fixtures ?')
				))
			->setName('nodevo:generate:fixtures')
			->setDescription('Génération des DataFixtures pour les entités du bundle')
		;
	}

    /**
     * [execute description]
     *
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     *
     * @return [type]
     */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $bundle;
        // Récupération du bundle d'après le nom saisi
        $bundles = $this->getContainer()->get('kernel')->getBundles();
        foreach ($bundles as $bundleTemp) {
            if ($bundleTemp->getName() == $input->getOption('bundle')){
                $bundle = $bundleTemp;
                break;
            }
        }

		$allEntities  = array();
        $dependencies = array();

        $em = $this->getContainer()->get('doctrine')->getManager();

        // Récupération des entités présentes dans ce bundle
        foreach ($em->getMetadataFactory()->getAllMetadata() as $meta) {
            $allEntities[] = array(
                'entity'    => $meta, 
                'shortName' => $em->getMetadataFactory()->getReflectionService()->getClassShortName($meta->getName())
            );
        }

        // Récupération des associations de chaque entité
        foreach ($allEntities as $entity) { 
            $doctrineEntity      = $entity['entity'];
            $name                = $doctrineEntity->getName();
            $dependencies[$name] = array();

            foreach ($doctrineEntity->getAssociationNames() as $association) {
                if ($doctrineEntity->isAssociationInverseSide($association))
                    continue;

                if ($doctrineEntity->getAssociationTargetClass($association) === $name)
                    continue;
                
                $dependencies[$name][] = $doctrineEntity->getAssociationTargetClass($association);
            }
        }

        $allEntities = $this->determineOrder($allEntities, $dependencies);
        $entities    = $this->filtreEntities($allEntities, $bundle->getNamespace());

        if (count($entities) == 0)
        {
            $output->writeln('Génération des fixtures : <info>Aucune entité concrète trouvrée dans ce bundle</info>');
            return;
        }

        // Récupération des données de chaque entité
        foreach ($entities as $key => $entity) {
           $entities[$key]['data'] = $em->getRepository($entity['entity']->getName())->findAll();
        }

        $erase = $input->getOption('erase') == 1;
        
        $generator         = $this->getGenerator();
        $generatedFixtures = $generator->generate($bundle, $entities, $em->getMetadataFactory(), $erase);
        
        if (count($generatedFixtures) > 0)
        {
            $output->writeln('Génération des fixtures : <info>OK</info>');
            $output->writeln('Fixtures générées : ');

            foreach ($generatedFixtures as $fixture)
            {
                $output->writeln('<info>'.$fixture.'</info>');
            }
        }
        else
        {
            $output->writeln('0 fixtures générées');
        }        
	}

    /**
     * [interact description]
     *
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     *
     * @return [type]
     */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getDialogHelper();
		$dialog->writeSection($output, 'Welcome to the Nodevo Fixtures generator');

		$output->writeln(array(
            '',
            'This command helps you generate data fixtures.',
            '',
            'You need to give the bundle for which you want to generate data fixtures.',
            '',
            'Like <comment>AcmeBlogBundle</comment>.',
            '',
        ));

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        $bundle = $dialog->askAndValidate($output, $dialog->getQuestion('The Bundle shortcut name', $input->getOption('bundle')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName'), false, $input->getOption('bundle'), $bundleNames);
        $input->setOption('bundle', $bundle);

        $erase = $dialog->askConfirmation($output, $dialog->getQuestion('Erase existing fixtures (yes, no)?', 'yes', '?'), true);
        $input->setOption('erase', $erase);
	}

	/**
     * Prends le chemin du skeleton de notre ToolsBundle
     */
    protected function getGenerator(BundleInterface $bundle = null)
    {
        if (null === $this->generator) {
            $path = $this->getContainer()->get('kernel')->locateResource('@NodevoToolsBundle/Resources/skeleton/');

            $this->generator = $this->createGenerator();
            $skeletonDirs    = array($path);

            $this->generator->setSkeletonDirs( $skeletonDirs );
        }

        return $this->generator;
    }

	/**
     * Créer un generator de type DataFixtures
     *
     * @return DataFixturesGenerator
     */
    protected function createGenerator()
    {
        return new DataFixturesGenerator($this->getContainer()->get('filesystem'));
    }

    /**
    * Filtre les entités pour ne retenir que celle qui appartiennent au bundle choisi par l'utilisateur
    */
    private function filtreEntities($entities, $bundleNamespace)
    {
        $result = array();

        foreach ($entities as $entity) {
            $doctrineEntity = $entity['entity'];

            if (strpos($doctrineEntity->getName(), $bundleNamespace) !== 0)
                continue;

            $result[] = $entity;
        }

        return $result;
    }

    /*
    * Affecte un ordre de génération à chaque entité en fonction de sa dépendance aux autres entités
    */
    private function determineOrder($allEntities, $dependencies)
    {
        $orders = array();

        foreach ($allEntities as $entity){
            $doctrineEntity = $entity['entity'];

            $orders[$doctrineEntity->getName()] = $this->updateOrder($doctrineEntity->getName(), $dependencies);
        }

        $orders = $this->reorderArray($orders);

        foreach ($allEntities as $key => $entity){
            $allEntities[$key]['order'] = $orders[$entity['entity']->getName()];
        }

        return $allEntities;
    }

    /*
    * Modifie l'ordre de génération d'une entité ( une entité doit être générée après toutes les entités vers lesquelles elle a une association de type many-to-one )
    */
    private function updateOrder($entity, $dependencies)
    {
        $maxOrder = 1;

        if (isset($dependencies[$entity])) {
            foreach ($dependencies[$entity] as $dependency) {
                $order = $this->updateOrder($dependency, $dependencies);

                if ($order >= $maxOrder)
                    $maxOrder = $order + 1;
            }
        }        

        return $maxOrder;
    }

    /*
    * Ordonne le tableau d'ordres pour obtenir une suite continue
    */
    private function reorderArray($orders)
    {
        asort($orders);

        $order = 1;

        foreach ($orders as $key => $value) {
            $orders[$key] = $order;
            $order++;
        }

        return $orders;
    }
}
