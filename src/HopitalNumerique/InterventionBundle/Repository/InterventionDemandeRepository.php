<?php
/**
 * InterventionDemandeRepository
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * InterventionDemandeRepository
 */
class InterventionDemandeRepository extends EntityRepository
{
    /**
     * Récupère les données du grid des nouvelles demandes d'intervention sous forme de tableau correctement formaté
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI des demandes d'intervention
     * @return array
     */
    public function getGridDonnees_DemandesNouvelles(User $cmsi)
    {
        //$demandesInitiales = array();
        
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(referent.nom, \' \', referent.prenom, \' (\', referentEtablissement.nom, \', \', referentRegion.libelle, \')\') AS demandeurInformations',
                'CONCAT(ambassadeur.nom, \' \', ambassadeur.prenom, \' (\', ambassadeurRegion.libelle, \')\') AS ambassadeurInformations',
                'objet.id AS objetId',
                //'objet.titre AS objetTitre',
                'GROUP_CONCAT(objet.titre) AS objetsInformations',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                ->innerJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->innerJoin('referent.region', 'referentRegion')
                // Ambassadeur
                ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
                ->innerJoin('ambassadeur.region', 'ambassadeurRegion')
                // Objets
                ->leftJoin('interventionDemande.objets', 'objet')
                // État
                ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
            ->where('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $cmsi)
            ->andWhere('interventionEtat.id = :interventionEtatDemandeInitiale OR interventionEtat.id = :interventionEtatAttenteCmsi')
                ->setParameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setParameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
            ->groupBy('interventionDemande.id')
        ;
        
        
        
        
        
        return $requete->getQUery()->getResult();
        
        
        
        
        /*$interventionDemandeId = null;
        foreach ($requete->getQUery()->getResult() as $resultat)
        {
            // Demande suivante
            if ($resultat['id'] != $interventionDemandeId)
            {
                $interventionDemandeId = $resultat['id'];
                $demandesInitiales[] = $resultat;
                $demandesInitiales[count($demandesInitiales) - 1]['objetsInformations'] = '';
            }
            if ($resultat['objetId'] != null)
                $demandesInitiales[count($demandesInitiales) - 1]['objetsInformations'] .= '<div>'.$resultat['objetTitre'].'</div>';
        }
        
        return $demandesInitiales;*/
    }
    
    /**
     * Récupère les données du grid des demandes d'intervention traitées sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI des demandes d'intervention
     * @return array
     */
    public function getGridDonnees_DemandesTraitees(User $cmsi)
    {
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(referent.nom, \' \', referent.prenom, \' (\', referentEtablissement.nom, \', \', referentRegion.libelle, \')\') AS demandeurInformations',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(ambassadeur.nom, \' \', ambassadeur.prenom, \' (\', ambassadeurRegion.libelle, \')\') AS ambassadeurInformations',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.libelle AS evaluationEtatLibelle',
                'remboursementEtat.libelle AS remboursementEtatLibelle'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                ->innerJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->innerJoin('referent.region', 'referentRegion')
                // Initiateur
                ->innerJoin('interventionDemande.interventionInitiateur', 'interventionInitiateur')
                // Ambassadeur
                ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
                ->innerJoin('ambassadeur.region', 'ambassadeurRegion')
                // État de l'intervention
                ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
                // État de l'évaluation
                ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
                // État du remboursement
                ->leftJoin('interventionDemande.remboursementEtat', 'remboursementEtat')
            ->where('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $cmsi)
            ->andWhere('interventionEtat.id != :interventionEtatDemandeInitiale AND interventionEtat.id != :interventionEtatAttenteCmsi')
                ->setParameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setParameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
        ;

        return $requete->getQUery()->getResult();
    }
}
