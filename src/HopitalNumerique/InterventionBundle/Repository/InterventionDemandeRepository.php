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
use Doctrine\ORM\Query\Expr\Join;

/**
 * InterventionDemandeRepository
 */
class InterventionDemandeRepository extends EntityRepository
{
    /**
     * Retourne les demandes d'intervention qui doivent automatiquement être validées par le CMSI.
     * 
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention qui doivent automatiquement être validées par le CMSI
     */
    public function findByDemandesInitialesAValiderCmsi()
    {
        $requete = $this->_em->createQueryBuilder();
        
        $requete
            ->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.interventionEtat = :interventionEtat')
                ->setParameter('interventionEtat', InterventionEtat::getInterventionEtatDemandeInitialeId())
            ->andWhere(':aujourdhui > DATE_ADD(interventionDemande.dateCreation, '.InterventionEtat::$VALIDATION_CMSI_NOMBRE_JOURS.', \'day\')')
                ->setParameter('aujourdhui', new \DateTime())
        ;

        return $requete->getQUery()->getResult();
    }
    /**
     * Retourne les demandes d'intervention en attente CMSI pour une relance.
     * 
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention qui doivent automatiquement être validées par le CMSI
     */
    public function findByEtatAttenteCmsiPourRelance()
    {
        $requete = $this->_em->createQueryBuilder();
        
        $requete
            ->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.interventionEtat = :interventionEtat')
                ->setParameter('interventionEtat', InterventionEtat::getInterventionEtatAttenteCmsiId())
            //->andWhere('(interventionDemande.cmsiDateDerniereRelance IS NOT NULL AND :aujourdhui > DATE_ADD(interventionDemande.cmsiDateDerniereRelance, '.InterventionEtat::$NOTIFICATION_MISE_EN_ATTENTE_CMSI_NOMBRE_JOURS.', \'day\')) OR (interventionDemande.cmsiDateDerniereRelance IS NULL AND :aujourdhui > DATE_ADD(interventionDemande.dateCreation, '.InterventionEtat::$NOTIFICATION_MISE_EN_ATTENTE_CMSI_NOMBRE_JOURS.', \'day\'))')
            ->andWhere(':aujourdhui > DATE_ADD(interventionDemande.cmsiDateDerniereRelance, '.InterventionEtat::$NOTIFICATION_MISE_EN_ATTENTE_CMSI_NOMBRE_JOURS.', \'day\')')
                ->setParameter('aujourdhui', new \DateTime())
        ;

        return $requete->getQUery()->getResult();
    }
    /**
     * Retourne les demandes d'intervention acceptée par le CMSI pour une relance.
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention qui doivent être répondues par l'ambassadeur
     */
    public function findByEtatAcceptationCmsiPourRelance()
    {
        $requete = $this->_em->createQueryBuilder()
            ->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.interventionEtat = :interventionEtat')
                ->setParameter('interventionEtat', InterventionEtat::getInterventionEtatAcceptationCmsiId())
                ->andWhere(':aujourdhui > DATE_ADD(interventionDemande.ambassadeurDateDerniereRelance, '.InterventionEtat::$NOTIFICATION_AVANT_RELANCE_AMBASSADEUR_1_NOMBRE_JOURS.', \'day\')')
            ->setParameter('aujourdhui', new \DateTime())
        ;
        
        return $requete->getQUery()->getResult();
    }
    
    /**
     * Récupère les données du grid des nouvelles demandes d'intervention pour le CMSI sous forme de tableau correctement formaté
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI des demandes d'intervention
     * @return array
     */
    public function getGridDonnees_CmsiDemandesNouvelles(User $cmsi)
    {
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(\'<strong>\', referent.nom, \' \', referent.prenom, \'</strong><br>\', referentEtablissement.nom, \' (\', referentRegion.libelle, \')\') AS demandeurInformations',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurRegion.libelle) AS ambassadeurInformations',
                'objet.id AS objetId',
                'GROUP_CONCAT(objet.titre) AS objetsInformations',
                'interventionEtat.id AS interventionEtatId',
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
            ->orderBy('interventionDemande.dateCreation', 'DESC')
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
     * Récupère les données du grid des demandes d'intervention traitées pour le CMSI sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI des demandes d'intervention
     * @return array
     */
    public function getGridDonnees_CmsiDemandesTraitees(User $cmsi)
    {
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(\'<strong>\', referent.nom, \' \', referent.prenom, \'</strong><br>\', referentEtablissement.nom, \' (\', referentRegion.libelle, \')\') AS demandeurInformations',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurRegion.libelle) AS ambassadeurInformations',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.libelle AS evaluationEtatLibelle',
                'remboursementEtat.libelle AS remboursementEtatLibelle',
                'COUNT(interventionRegroupement) AS nombreRegroupements'
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
                // Regroupement
                ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement', Join::WITH, 'interventionDemande.id = interventionRegroupement.interventionDemandePrincipale')
            ->where('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $cmsi)
            ->andWhere('interventionEtat.id != :interventionEtatDemandeInitiale AND interventionEtat.id != :interventionEtatAttenteCmsi')
                ->setParameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setParameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
            ->orderBy('interventionDemande.dateCreation', 'DESC')
        ;

        return $requete->getQUery()->getResult();
    }
    /**
     * Récupère les données du grid des demandes d'intervention pour l'ambassadeur sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur des demandes d'intervention
     * @return array
     */
    public function getGridDonnees_AmbassadeurDemandes(User $ambassadeur)
    {
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(\'<strong>\', referent.nom, \' \', referent.prenom, \'</strong><br>\', referentEtablissement.nom, \' (\', referentRegion.libelle, \')\') AS demandeurInformations',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'remboursementEtat.libelle AS remboursementEtatLibelle'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                ->innerJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->innerJoin('referent.region', 'referentRegion')
                // Initiateur
                ->innerJoin('interventionDemande.interventionInitiateur', 'interventionInitiateur')
                // État de l'intervention
                ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
                // État de l'évaluation
                ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
                // État du remboursement
                ->leftJoin('interventionDemande.remboursementEtat', 'remboursementEtat')
            ->where('interventionDemande.ambassadeur = :ambassadeur')
                ->setParameter('ambassadeur', $ambassadeur)
            ->andWhere("
                interventionEtat.id = :interventionEtatAcceptationCmsi
                OR interventionEtat.id = :interventionEtatAcceptationCmsiRelance1
                OR interventionEtat.id = :interventionEtatAcceptationCmsiRelance2
                OR interventionEtat.id = :interventionEtatRefusAmbassadeur
                OR interventionEtat.id = :interventionEtatAcceptationAmbassadeur
                OR interventionEtat.id = :interventionEtatTermine
                OR interventionEtat.id = :interventionEtatCloture
            ")
                ->setParameter('interventionEtatAcceptationCmsi', InterventionEtat::getInterventionEtatAcceptationCmsiId())
                ->setParameter('interventionEtatAcceptationCmsiRelance1', InterventionEtat::getInterventionEtatAcceptationCmsiRelance1Id())
                ->setParameter('interventionEtatAcceptationCmsiRelance2', InterventionEtat::getInterventionEtatAcceptationCmsiRelance2Id())
                ->setParameter('interventionEtatRefusAmbassadeur', InterventionEtat::getInterventionEtatRefusAmbassadeurId())
                ->setParameter('interventionEtatAcceptationAmbassadeur', InterventionEtat::getInterventionEtatAcceptationAmbassadeurId())
                ->setParameter('interventionEtatTermine', InterventionEtat::getInterventionEtatTermineId())
                ->setParameter('interventionEtatCloture', InterventionEtat::getInterventionEtatClotureId())
            ->orderBy('interventionDemande.dateCreation', 'DESC')
        ;
    
        return $requete->getQUery()->getResult();
    }
}
