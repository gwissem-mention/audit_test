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
     * @todo COMMENTER
     */
    public function estInterventionDemandeRegroupee(InterventionDemande $interventionDemande)
    {
        $interventionRegroupements = $this->_repository->findBy(array('interventionDemandeRegroupee' => $interventionDemande));
        
        return (count($interventionRegroupements) > 0);
    }
}
