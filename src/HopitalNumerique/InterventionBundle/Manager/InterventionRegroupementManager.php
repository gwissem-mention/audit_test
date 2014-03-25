<?php
/**
 * Manager pour les regroupements d'interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\UserBundle\Entity\User;

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
        return (count($interventionDemande->getInterventionRegroupementsDemandesPrincipales()) > 0);
    }
    
    /**
     * Retourne si une des demandes principales des regroupements d'intervention a ce référent.
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionDemande $interventionDemande La demande d'intervention possédant les regroupements à vérifier
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Référent à vérifier
     * @return boolean VRAI ssi le référent est présent dans une demande principale.
     */
    public function interventionRegroupementsDemandePrincipaleHaveReferent(InterventionDemande $interventionDemande, User $referent)
    {
        foreach ($interventionDemande->getInterventionRegroupementsDemandesPrincipales() as $interventionRegroupementsDemandePrincipale)
        if ($interventionRegroupementsDemandePrincipale->getInterventionDemandePrincipale()->getReferent()->getId() == $referent->getId())
            return true;
        return false;
    }
}
