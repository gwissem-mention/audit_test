<?php

namespace HopitalNumerique\ExpertBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;

/**
 * Manager de l'entité ActiviteExpert.
 */
class ActiviteExpertManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ExpertBundle\Entity\ActiviteExpert';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $activiteExpertsForGrid = array();

        $activiteExperts = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        foreach ($activiteExperts as $activiteExpert) 
        {
            $activiteExpert['experts'] = $activiteExpert['expertPrenom'] . ' ' . $activiteExpert['expertNom'];
            $activiteExpert['anapiens'] = $activiteExpert['anapPrenom'] . ' ' . $activiteExpert['anapNom'];
            unset($activiteExpert['expertPrenom']);
            unset($activiteExpert['expertNom']);
            unset($activiteExpert['anapPrenom']);
            unset($activiteExpert['anapNom']);

            if(!array_key_exists($activiteExpert['id'], $activiteExpertsForGrid))
            {
                $activiteExpertsForGrid[$activiteExpert['id']] = $activiteExpert;
            }
            else
            {
                if((strpos($activiteExpertsForGrid[$activiteExpert['id']]['anapiens'],$activiteExpert['anapiens']) === false))
                {
                    $activiteExpertsForGrid[$activiteExpert['id']]['anapiens'] .= ";" . $activiteExpert['anapiens'];
                }
                if((strpos($activiteExpertsForGrid[$activiteExpert['id']]['experts'],$activiteExpert['experts']) === false))
                {
                    $activiteExpertsForGrid[$activiteExpert['id']]['experts'] .= ";" . $activiteExpert['experts'];
                }
            }
        }

        return array_values($activiteExpertsForGrid);
    }

    /**
     * Récupération de l'ensemble des experts lié à l'activité sous forme de tableau array(id)= prenom NOM
     *
     * @param ActiviteExpert $activiteExpert [description]
     *
     * @return array
     */
    public function getExperts(ActiviteExpert $activiteExpert)
    {
        $experts = array();

        foreach ($activiteExpert->getExpertConcernes() as $expert)
        {
            $experts[$expert->getId()] = $expert->getPrenom() . ' ' .strtoupper($expert->getNom()); 
        }

        return $experts;
    }

    /**
     * Récupération des experts et de leur vacations pour la facture par Activité
     *
     * @param ActiviteExpert $activiteExpert [description]
     *
     * @return [type]
     */
    public function getExpertsAndVacationForActivite(ActiviteExpert $activiteExpert)
    {
        $experts = array();
        $compteurDateFictive = 0;

        foreach ($activiteExpert->getEvenements() as $evenement) 
        {
            foreach ($evenement->getExperts() as $expertPresence) 
            {
                if(!array_key_exists($expertPresence->getExpertConcerne()->getId(), $experts))
                {
                    $experts[$expertPresence->getExpertConcerne()->getId()] = array();
                }
                if($expertPresence->getPresent())
                {
                    $experts[$expertPresence->getExpertConcerne()->getId()][$evenement->getDate()->format('d/m/y')] = array(
                        'fictive'     => 'false',
                        'nbVacations' => $evenement->getNbVacation()
                    );
                }
                else
                {
                    for ($i=1; $i <= $evenement->getNbVacation(); $i++) {
                        $experts[$expertPresence->getExpertConcerne()->getId()][$activiteExpert->getDateFictives()[$compteurDateFictive]->getDate()->format('d/m/y')] = array(
                            'fictive'     => 'true',
                            'nbVacations' => 1
                        );
                        $compteurDateFictive++;
                    }
                }
            }
        }

        foreach ($experts as &$expert) 
        {
            ksort($expert);
        }

        return $experts;
    }
    
    /**
     * Recupération des activités concernant l'expert
     *
     * @param int $expertId Identifiant de l'expert
     *
     * @return [type]
     */
    public function getActivitesForExpert($idExpert)
    {
        return $this->getRepository()->getActivitesForExpert($idExpert)->getQuery()->getResult();
    }
    
    /**
     * Recupération des activités concernant l'anapien
     *
     * @param int $expertId Identifiant de l'anapien
     *
     * @return [type]
     */
    public function getActivitesForAnapien($idAnapien)
    {
        return $this->getRepository()->getActivitesForAnapien($idAnapien)->getQuery()->getResult();
    }

    /**
     * Retourne le CSV (fichier temporaire) du contrat.
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activiteExpert Expert
     * @return string Chemin du fichier CSV
     */
    public function getContratCsv(ActiviteExpert $activiteExpert)
    {
        $csvFile = tmpfile();
        $csvPath = stream_get_meta_data(tmpfile())['uri'];

        $anapienNoms = array();
        foreach ($activiteExpert->getAnapiens() as $anapien) {
            $anapienNoms[] = $anapien->getPrenomNom();
        }

        $csvColumns = array(
            'Titre',
            'Type d\'activité',
            'Date de début',
            'Date de fin',
            'Nombre de vacations par expert',
            'Prestataire affecté',
            'Unité d\'oeuvre concernée',
            'Anapiens référents',
            'État',
            'Expert concerné - Prénom',
            'Expert concerné - Nom',
            'Expert concerné - Adresse électronique'
        );

        $csvData = array();
        foreach ($activiteExpert->getExpertConcernes() as $expertConcerne) {
            $csvData[] = array(
                $activiteExpert->getTitre(),
                $activiteExpert->getTypeActivite(),
                (null !== $activiteExpert->getDateDebut() ? $activiteExpert->getDateDebut()->format('d/m/Y') : ''),
                (null !== $activiteExpert->getDateFin() ? $activiteExpert->getDateFin()->format('d/m/Y') : ''),
                $activiteExpert->getNbVacationParExpert(),
                $activiteExpert->getPrestataire(),
                $activiteExpert->getUniteOeuvreConcerne(),
                implode(', ', $anapienNoms),
                $activiteExpert->getEtat(),
                $expertConcerne->getPrenom(),
                $expertConcerne->getNom(),
                $expertConcerne->getEmail()
            );
        }

        $csvResponse = $this->exportCsv($csvColumns, $csvData, 'activite.csv', 'ISO-8859-1');
        $csvContent = $csvResponse->getContent();
        fwrite($csvFile, $csvContent);

        return $csvPath;
    }
}
