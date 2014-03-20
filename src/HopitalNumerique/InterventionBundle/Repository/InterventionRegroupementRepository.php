<?php
/**
 * InterventionRegroupementRepository
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * InterventionRegroupementRepository
 */
class InterventionRegroupementRepository extends EntityRepository
{
    /**
     * Retourne si une demande d'intervention est une demande qui a été regroupée ou non.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à vérifiée
     * @return boolean VRAI ssi la demande d'intervention est une demande qui a été regroupée
     */
    public function estInterventionDemandeRegroupee(InterventionDemande $interventionDemande)
    {
        $requete = $this->_em->createQueryBuilder();
        
        $requete->select('COUNT(interventionRegroupement)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement')
            ->where('interventionRegroupement.interventionDemandeRegroupee = :interventionDemande')
                ->setParameter('interventionDemande', $interventionDemande);

        $interventionRegroupeeTotal = intval($requete->getQuery()->getSingleScalarResult());
        
        return ($interventionRegroupeeTotal > 0);
    }
}
