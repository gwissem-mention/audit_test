<?php

namespace HopitalNumerique\ReferenceBundle\Command;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Init reference domain display.
 */
class InitReferenceDomaineDisplayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hn:reference:display')
            ->setDescription('Initialise toutes les références avec leurs domaines visibles par défaut.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $em = $container->get('doctrine.orm.entity_manager');

        $references = $container->get('hopitalnumerique_reference.manager.reference')->findAll();
        $domaines = $container->get('hopitalnumerique_domaine.manager.domaine')->findAll();

        foreach ($references as $reference) {
            /** @var Reference $reference */
            if ($reference->isAllDomaines()) {
                $referenceDomaine = $domaines;
            } else {
                $referenceDomaine = $reference->getDomaines();
            }

            foreach ($referenceDomaine as $domaine) {
                $reference->addDomainesDisplay($domaine);
            }
            $em->persist($reference);
        }

        $em->flush();

        $output->writeln('Done...');
    }
}
