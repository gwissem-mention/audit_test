<?php
/**
 * InterventionDemandeRepository
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr\Join;

/**
 * InterventionDemandeRepository
 */
class InterventionDemandeRepository extends EntityRepository
{
    /**
     * Retourne la liste des interventions de l'utilisateur
     *
     * @return QueryBuilder
     */
    public function getForFactures( $user = null )
    {
        $qb = $this->_em->createQueryBuilder()
                         ->select('interventionDemande')
                         ->from('\HopitalNumerique\InterventionBundle\Entity\InterventionDemande', 'interventionDemande')
                         ->leftJoin('interventionDemande.remboursementEtat', 'refRemboursement')
                         ->leftJoin('interventionDemande.interventionEtat', 'refEtat')
                         ->leftJoin('interventionDemande.evaluationEtat', 'refEvaluation')
                         ->andWhere('refRemboursement.id = 5 ', 'refEtat.id = 22')
                         ->andWhere('refEvaluation.id = 29 ', 'interventionDemande.facture IS NULL')
                         ->orderBy('interventionDemande.dateCreation');

        if( !is_null($user) ){
            $qb->andWhere('interventionDemande.ambassadeur = :user')
               ->setParameter('user', $user);
        }

        return $qb;
    }

    /**
     * Retourne la liste des interventions de l'utilisateur
     *
     * @return QueryBuilder
     */
    public function getForTotal( $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('interventionDemande')
                         ->from('\HopitalNumerique\InterventionBundle\Entity\InterventionDemande', 'interventionDemande')
                         ->leftJoin('interventionDemande.remboursementEtat', 'refRemboursement')
                         ->andWhere('interventionDemande.remboursementEtat IS NULL OR refRemboursement.id != 7')
                         ->andWhere('interventionDemande.referent = :user')
                         ->setParameter('user', $user)
                         ->orderBy('interventionDemande.dateCreation');
    }

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
     * Retourne les demandes d'intervention en relance ambassadeur 1 pour un seconde relance.
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention qui doivent être répondues par l'ambassadeur
     */
    public function findByEtatRelanceAmbassadeur1PourRelance()
    {
        $requete = $this->_em->createQueryBuilder()
            ->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.interventionEtat = :interventionEtat')
                ->setParameter('interventionEtat', InterventionEtat::getInterventionEtatAcceptationCmsiRelance1Id())
            ->andWhere(':aujourdhui > DATE_ADD(interventionDemande.ambassadeurDateDerniereRelance, '.InterventionEtat::$NOTIFICATION_AVANT_RELANCE_AMBASSADEUR_2_NOMBRE_JOURS.', \'day\')')
                ->setParameter('aujourdhui', new \DateTime())
        ;
    
        return $requete->getQUery()->getResult();
    }
    /**
     * Retourne les demandes d'intervention en relance ambassadeur 2 pour leur clôture.
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention qui doivent être clôturées car sans réponse de leur ambassadeur
     */
    public function findByEtatRelanceAmbassadeur2PourRelance()
    {
        $requete = $this->_em->createQueryBuilder()
            ->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.interventionEtat = :interventionEtat')
                ->setParameter('interventionEtat', InterventionEtat::getInterventionEtatAcceptationCmsiRelance2Id())
            ->andWhere(':aujourdhui > DATE_ADD(interventionDemande.ambassadeurDateDerniereRelance, '.InterventionEtat::$NOTIFICATION_AVANT_RELANCE_AMBASSADEUR_CLOTURE_NOMBRE_JOURS.', \'day\')')
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
    public function getGridDonneesCmsiDemandesNouvelles(User $cmsi)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
            ;
        
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(referent.nom, \' \', referent.prenom) AS referent_nom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurRegion.libelle) AS ambassadeurInformations',
                'objet.id AS objetId',
                'GROUP_CONCAT(objet.titre) AS objetsInformations',
                'interventionEtat.id AS interventionEtatId',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'COUNT(interventionRegroupementRegroupee) AS nombreDemandesRegroupees',
                'COUNT(interventionRegroupementPrincipale) AS nombreDemandesPrincipales'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                    ->leftJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                    ->leftJoin('referent.region', 'referentRegion')
                // Ambassadeur
                ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
                ->innerJoin('ambassadeur.region', 'ambassadeurRegion')
                // Objets
                ->leftJoin('interventionDemande.objets', 'objet')
                // État
                ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
            // Regroupements
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementRegroupee', Join::WITH, 'interventionDemande.id = interventionRegroupementRegroupee.interventionDemandePrincipale')
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipale', Join::WITH, 'interventionDemande.id = interventionRegroupementPrincipale.interventionDemandeRegroupee')
            ->where('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $cmsi)
            ->andWhere('interventionEtat.id = :interventionEtatDemandeInitiale OR interventionEtat.id = :interventionEtatAttenteCmsi')
                ->setParameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setParameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupees->getDQL()
                )
            )
            ->orderBy('interventionDemande.dateCreation', 'DESC')
            ->groupBy('interventionDemande.id')
        ;

        return $requete->getQUery()->getResult();
    }
    /**
     * Récupère les données du grid des demandes d'intervention traitées pour le CMSI sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI des demandes d'intervention
     * @return array
     */
    public function getGridDonneesCmsiDemandesTraitees(User $cmsi)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
            ;
        
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'referent.id AS referentId',
                'CONCAT(referent.nom, \' \', referent.prenom) AS referent_nom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurRegion.libelle) AS ambassadeurInformations',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.evaluationDate, \'\') AS evaluationDateLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'remboursementEtat.libelle AS remboursementEtatLibelle',
                'COUNT(interventionRegroupementRegroupee) AS nombreDemandesRegroupees',
                'COUNT(interventionRegroupementPrincipale) AS nombreDemandesPrincipales'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                ->leftJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->leftJoin('referent.region', 'referentRegion')
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
            // Regroupements
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementRegroupee', Join::WITH, 'interventionDemande.id = interventionRegroupementRegroupee.interventionDemandePrincipale')
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipale', Join::WITH, 'interventionDemande.id = interventionRegroupementPrincipale.interventionDemandeRegroupee')
            ->where('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $cmsi)
            ->andWhere('interventionEtat.id != :interventionEtatDemandeInitiale AND interventionEtat.id != :interventionEtatAttenteCmsi')
                ->setParameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setParameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupees->getDQL()
                )
            )
            ->orderBy('interventionDemande.dateCreation', 'DESC')
            ->groupBy('interventionDemande.id')
        ;

        return $requete->getQUery()->getResult();
    }
    /**
     * Récupère les données du grid des suivis de demandes d'intervention pour le directeur sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $directeur Le directeur des demandes d'intervention
     * @return array
     */
    public function getGridDonneesDirecteurSuiviDemandes(User $directeur)
    {
        $requete = $this->_em->createQueryBuilder();
    
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(referent.nom, \' \', referent.prenom) AS referent_nom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurEtablissement.nom, \' - \', ambassadeurEtablissement.finess, \' (\', ambassadeurRegion.libelle, \')\') AS ambassadeurInformations',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.evaluationDate, \'\') AS evaluationDateLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'remboursementEtat.libelle AS remboursementEtatLibelle',
                'COUNT(interventionRegroupementRegroupee) AS nombreDemandesRegroupees',
                'COUNT(interventionRegroupementPrincipale) AS nombreDemandesPrincipales'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.directeur = :directeur')
                ->setParameter('directeur', $directeur)
            // Référent
            ->innerJoin('interventionDemande.referent', 'referent')
                ->leftJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->leftJoin('referent.region', 'referentRegion')
            // Ambassadeur
            ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
            ->innerJoin('ambassadeur.etablissementRattachementSante', 'ambassadeurEtablissement')
            ->innerJoin('ambassadeur.region', 'ambassadeurRegion')
            // Initiateur
            ->innerJoin('interventionDemande.interventionInitiateur', 'interventionInitiateur')
            // État de l'intervention
            ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
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
            // État de l'évaluation
            ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
            // État du remboursement
            ->leftJoin('interventionDemande.remboursementEtat', 'remboursementEtat')
            // Regroupements
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementRegroupee', Join::WITH, 'interventionDemande.id = interventionRegroupementRegroupee.interventionDemandePrincipale')
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipale', Join::WITH, 'interventionDemande.id = interventionRegroupementPrincipale.interventionDemandeRegroupee')
            ->orderBy('interventionDemande.dateCreation', 'DESC')
            ->groupBy('interventionDemande.id')
        ;
    
        return $requete->getQUery()->getResult();
    }
    /**
     * Récupère les données du grid des demandes d'intervention pour l'ambassadeur sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur des demandes d'intervention
     * @return array
     */
    public function getGridDonneesAmbassadeurDemandes(User $ambassadeur)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
            ;
        
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(referent.nom, \' \', referent.prenom) AS referent_nom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.evaluationDate, \'\') AS evaluationDateLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'remboursementEtat.libelle AS remboursementEtatLibelle',
                'COUNT(interventionRegroupementRegroupee) AS nombreDemandesRegroupees',
                'COUNT(interventionRegroupementPrincipale) AS nombreDemandesPrincipales'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                    ->leftJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                    ->leftJoin('referent.region', 'referentRegion')
                // Initiateur
                ->innerJoin('interventionDemande.interventionInitiateur', 'interventionInitiateur')
                // État de l'intervention
                ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
                // État de l'évaluation
                ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
                // État du remboursement
                ->leftJoin('interventionDemande.remboursementEtat', 'remboursementEtat')
            // Regroupements
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementRegroupee', Join::WITH, 'interventionDemande.id = interventionRegroupementRegroupee.interventionDemandePrincipale')
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipale', Join::WITH, 'interventionDemande.id = interventionRegroupementPrincipale.interventionDemandeRegroupee')
            ->where('interventionDemande.ambassadeur = :ambassadeur')
                ->setParameter('ambassadeur', $ambassadeur)
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupees->getDQL()
                )
            )
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
            ->groupBy('interventionDemande.id')
        ;
    
        return $requete->getQUery()->getResult();
    }
    /**
     * Récupère les données du grid des demandes d'intervention pour l'établissement sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Le référent de l'établissement des demandes d'intervention
     * @return array
     */
    public function getGridDonneesEtablissementDemandes(User $referent)
    {
        $demandesDoublonsIdsAvecMemeDemandePrincipale = $this->getDemandesDoublonsIdsAvecMemeDemandePrincipale($referent);
        
        // Ignorer les demandes groupées pour un même référent
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
                ->innerJoin('interventionRegroupementIgnore.interventionDemandeRegroupee', 'interventionDemandeRegroupee')
                    ->andWhere('interventionDemandeRegroupee.referent = :referent')
                ->innerJoin('interventionRegroupementIgnore.interventionDemandePrincipale', 'interventionDemandePrincipale')
                    ->andWhere('interventionDemandePrincipale.referent = :referent')
                ->setParameter('referent', $referent)
            ;

        $requete = $this->_em->createQueryBuilder()
            ->select(
                'interventionDemande.id AS id',
                'interventionInitiateur.id AS interventionInitiateurId',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurRegion.libelle) AS ambassadeurInformations',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.evaluationDate, \'\') AS evaluationDateLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'COUNT(interventionRegroupementRegroupee) AS nombreDemandesRegroupees',
                'COUNT(interventionRegroupementPrincipale) AS nombreDemandesPrincipales'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            // Référent
            ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
            ->innerJoin('ambassadeur.region', 'ambassadeurRegion')
            // Initiateur
            ->innerJoin('interventionDemande.interventionInitiateur', 'interventionInitiateur')
            // État de l'intervention
            ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
            // État de l'évaluation
            ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
            // Regroupements
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementRegroupee', Join::WITH, 'interventionDemande.id = interventionRegroupementRegroupee.interventionDemandePrincipale')
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipale', Join::WITH, 'interventionDemande.id = interventionRegroupementPrincipale.interventionDemandeRegroupee')
        ;

        $requete->where('interventionDemande.referent = :referent')
            ->setParameter('referent', $referent)
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupees->getDQL()
                )
            );
        if (count($demandesDoublonsIdsAvecMemeDemandePrincipale) > 0)
        {
            $requete->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $this->getDemandesDoublonsIdsAvecMemeDemandePrincipale($referent)
                )
            );
        }

        $requete->orderBy('interventionDemande.dateCreation', 'DESC')
            ->addGroupBy('interventionDemande.id')
            ;

        return $requete->getQUery()->getResult();
    }
    /**
     * Retourne les IDs des demandes d'intervention doublons (pas le premier résultat trouvé) qui possèdent la même demande d'intervention principale.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Le référent de l'établissement des demandes d'intervention
     * @return integer[] Les IDs des demandes doublons avec la même demande principale
     */
    private function getDemandesDoublonsIdsAvecMemeDemandePrincipale(User $referent)
    {
        $demandesDoublonsAvecMemeDemandePrincipaleIds = array();
        
        $requeteDemandesDoublonsAvecMemeDemandePrincipale = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementDoublonsAvecMemeDemandePrincipale.interventionDemandeRegroupee) AS demandeDoublonId')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementDoublonsAvecMemeDemandePrincipale')
                ->innerJoin('interventionRegroupementDoublonsAvecMemeDemandePrincipale.interventionDemandeRegroupee', 'interventionDoublonsDemandeRegroupee')
                    ->andWhere('interventionDoublonsDemandeRegroupee.referent = :referent')
                        ->setParameter('referent', $referent)
            ->orderBy('interventionDoublonsDemandeRegroupee.id', 'ASC')
            ->setFirstResult(1)
        ;
        foreach ($requeteDemandesDoublonsAvecMemeDemandePrincipale->getQuery()->getResult() as $demandeDoublon)
            $demandesDoublonsAvecMemeDemandePrincipaleIds[] = $demandeDoublon['demandeDoublonId'];

        return $demandesDoublonsAvecMemeDemandePrincipaleIds;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'administration.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesAdminDemandes()
    {
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select(
                'interventionDemande.id AS id',
                'interventionInitiateur.id AS interventionInitiateurId',
                'COUNT(interventionRegroupementRegroupee) AS nombreDemandesRegroupees',
                'COUNT(interventionRegroupementPrincipale) AS nombreDemandesPrincipales',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                
                'CONCAT(cmsi.nom, \' \', cmsi.prenom) AS cmsi_nom',
                'CONCAT(ambassadeur.nom, \' \', ambassadeur.prenom) AS ambassadeur_nom',
                'ambassadeurRegion.libelle AS ambassadeurRegionLibelle',
                'referent.id AS referentId',
                'CONCAT(referent.nom, \' \', referent.prenom) AS referent_nom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',

                'GROUP_CONCAT(objet.titre) AS objetsInformations',
                'interventionType.libelle AS interventionTypeLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.evaluationDate, \'\') AS evaluationDateLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'evaluationEtat.libelle AS evaluationEtatLibelle',
                'remboursementEtat.libelle AS remboursementEtatLibelle'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            // Initiateur
            ->innerJoin('interventionDemande.interventionInitiateur', 'interventionInitiateur')
            // État de l'intervention
            ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
            // CMSI
            ->innerJoin('interventionDemande.cmsi', 'cmsi')
            // Ambassadeur
            ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
                ->leftJoin('ambassadeur.region', 'ambassadeurRegion')
            // Référent
            ->innerJoin('interventionDemande.referent', 'referent')
                ->leftJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->leftJoin('referent.region', 'referentRegion')
            // Objets
            ->leftJoin('interventionDemande.objets', 'objet')
            // État de l'intervention
            ->innerJoin('interventionDemande.interventionType', 'interventionType')
            // État de l'évaluation
            ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
            // État du remboursement
            ->leftJoin('interventionDemande.remboursementEtat', 'remboursementEtat')
            // Regroupements
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementRegroupee', Join::WITH, 'interventionDemande.id = interventionRegroupementRegroupee.interventionDemandePrincipale')
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipale', Join::WITH, 'interventionDemande.id = interventionRegroupementPrincipale.interventionDemandeRegroupee')
            ->orderBy('interventionDemande.dateCreation', 'DESC')
            ->groupBy('interventionDemande.id')
        ;
    
        return $requete->getQUery()->getResult();
    }
    
    
    /**
     * Retourne les demandes d'intervention similaire par rapport aux objets d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut rechercher les demandes similaires
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention similaires par rapport aux objets
     */
    public function getInterventionsSimilairesParObjets(InterventionDemande $interventionDemande)
    {
        $objetIds = array();
        foreach ($interventionDemande->getObjets() as $objet)
            $objetIds[] = $objet->getId();

        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupement.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement')
        ;
        $requeteDemandesGroupementsPrincipales = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementPrincipal.interventionDemandePrincipale)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipal')
        ;
    
        $requete = $this->_em->createQueryBuilder();
        $requete->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.id != :interventionDemandeCourante')
                ->setParameter('interventionDemandeCourante', $interventionDemande->getId())
            ->andWhere('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $interventionDemande->getCmsi())
            ->andWhere('interventionDemande.interventionEtat = :interventionEtatDemandeInitiale OR interventionDemande.interventionEtat = :interventionEtatAttenteCmsi')
                ->setparameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setparameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupees->getDQL()
                )
            )
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupementsPrincipales->getDQL()
                )
            )
            ->leftJoin('interventionDemande.objets', 'objet');
        if (count($objetIds) > 0)
        {
            $requete->innerJoin('interventionDemande.objets', 'objetSimilaire')
                ->andWhere($requete->expr()->in(
                    'objetSimilaire',
                    implode(',', $objetIds)
                ));
        }
        $requete->having('COUNT(objet) = '.count($interventionDemande->getObjets()))
            ->orderBy('interventionDemande.dateCreation', 'ASC')
            ->groupBy('interventionDemande')
        ;
    
        return $requete->getQuery()->getResult();
    }
    /**
     * Retourne les demandes d'intervention similaire par rapport à l'ambassadeur d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut rechercher les demandes similaires
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention similaires par rapport à l'ambassadeur
     */
    public function getInterventionsSimilairesParAmbassadeur(InterventionDemande $interventionDemande)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupement.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement')
        ;
        $requeteDemandesGroupementsPrincipales = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementPrincipal.interventionDemandePrincipale)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementPrincipal')
        ;

        $requete = $this->_em->createQueryBuilder();
        $requete->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->where('interventionDemande.ambassadeur = :ambassadeur')
                ->setParameter('ambassadeur', $interventionDemande->getAmbassadeur())
            ->andWhere('interventionDemande.id != :interventionDemandeCourante')
                ->setParameter('interventionDemandeCourante', $interventionDemande->getId())
            ->andWhere('interventionDemande.cmsi = :cmsi')
                ->setParameter('cmsi', $interventionDemande->getCmsi())
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupees->getDQL()
                )
            )
            ->andWhere(
                $requete->expr()->notIn(
                    'interventionDemande',
                    $requeteDemandesGroupementsPrincipales->getDQL()
                )
            )
            ->andWhere('interventionDemande.interventionEtat = :interventionEtatDemandeInitiale OR interventionDemande.interventionEtat = :interventionEtatAttenteCmsi')
                ->setparameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setparameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
            ->orderBy('interventionDemande.dateCreation', 'ASC')
        ;

        return $requete->getQuery()->getResult();
    }
    
    /**
     * Retourne TRUE si le champ "Etat actuel" a été modifié
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention l'intervention en question
     * @return boolean TRUE si le champ "Etat actuel" a été modifié
     */
    public function isEtatActuelUpdated(InterventionDemande $intervention)
    {
        $requete = $this->_em->createQueryBuilder();
        $requete->select('interventionDemande')
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
            ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
            ->where('interventionDemande.id = :id')
                ->setParameter('id', $intervention->getId())
            ->andWhere('interventionEtat.id = :etat')
                ->setParameter('etat', $intervention->getInterventionEtat()->getId())
        ;
        return $requete->getQuery()->getOneOrNullResult() ? FALSE : TRUE;
    }

    /**
     * Retourne toutes les demandes pour l'export.
     *
     * @param integer[] $allPrimaryKeys Les IDs des demandes à exporter
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Demandes
     */
    public function findForExport(array $allPrimaryKeys)
    {
        if (count($allPrimaryKeys) == 0)
            return array();
        
        $requete = $this->createQueryBuilder('demande');
        
        $requete
            ->where($requete->expr()->in(
                'demande.id',
                $allPrimaryKeys
            ))
            ->leftJoin('demande.referent', 'referent')
            ->addSelect('referent')
            ->leftJoin('demande.ambassadeur', 'ambassadeur')
            ->addSelect('ambassadeur')
            ->leftJoin('demande.cmsi', 'cmsi')
            ->addSelect('cmsi')
            ->leftJoin('demande.directeur', 'directeur')
            ->addSelect('directeur')
            ->leftJoin('demande.interventionInitiateur', 'initiateur')
            ->addSelect('initiateur')
            ->leftJoin('demande.interventionType', 'interventionType')
            ->addSelect('interventionType')
            ->leftJoin('demande.interventionEtat', 'interventionEtat')
            ->addSelect('interventionEtat')
            ->leftJoin('demande.evaluationEtat', 'evaluationEtat')
            ->addSelect('evaluationEtat')
            ->leftJoin('demande.remboursementEtat', 'remboursementEtat')
            ->addSelect('remboursementEtat')
            ->leftJoin('demande.facture', 'facture')
            ->addSelect('facture')
            ->orderBy('demande.dateCreation', 'DESC')
        ;
        
        return $requete->getQuery()->getResult();
    }
}
