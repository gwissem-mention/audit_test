<?php

namespace HopitalNumerique\ExpertBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

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

}