<?php
/**
 * Manager pour les regroupements d'interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * Manager pour les regroupements d'interventions.
 */
class InterventionRegroupementManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement';

    /**
     * Retourne si une demande d'intervention est une demande qui a été regroupée ou non.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à vérifiée
     * @return boolean VRAI ssi la demande d'intervention est une demande qui a été regroupée
     */
    public function estInterventionDemandeRegroupee(InterventionDemande $interventionDemande)
    {
        return $this->_repository->estInterventionDemandeRegroupee($interventionDemande);
        /*$interventionRegroupements = $this->_repository->findBy(array('interventionDemandeRegroupee' => $interventionDemande));
        return (count($interventionRegroupements) > 0);*/
    }
}
