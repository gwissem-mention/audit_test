<?php
/**
 * Commande à exécuter périodiquement pour le fonctionnement des demandes d'intervention.
 * 
 * @author Rémi Leclerc
 */
namespace HopitalNumerique\InterventionBundle\Command\Intervention;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dataviz\Bundle\ImportExportBundle\Entity\FichierImporte;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Commande à exécuter périodiquement pour le fonctionnement des demandes d'intervention.
 */
class CronCommand extends ContainerAwareCommand
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('hn:intervention:cron')
            ->setDescription('CRON gérant les demandes d\'intervention')
        ;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('hopitalnumerique_intervention.manager.intervention_demande')->majInterventionEtatsDesInterventionDemandes();
    }
}