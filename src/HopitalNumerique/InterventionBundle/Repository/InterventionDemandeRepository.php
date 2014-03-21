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
    public function getGridDonnees_CmsiDemandesNouvelles(User $cmsi)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
            ;
        
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'referent.nom AS referentNom',
                'referent.prenom AS referentPrenom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
                'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurRegion.libelle) AS ambassadeurInformations',
                'objet.id AS objetId',
                'GROUP_CONCAT(objet.titre) AS objetsInformations',
                'interventionEtat.id AS interventionEtatId',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'COUNT(interventionRegroupement) AS nombreRegroupements'
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
                // Regroupement
                ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement', Join::WITH, 'interventionDemande.id = interventionRegroupement.interventionDemandePrincipale')
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
    public function getGridDonnees_CmsiDemandesTraitees(User $cmsi)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
            ;
        
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'referent.nom AS referentNom',
                'referent.prenom AS referentPrenom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
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
                // Regroupement
                ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement', Join::WITH, 'interventionDemande.id = interventionRegroupement.interventionDemandePrincipale')
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
    public function getGridDonnees_DirecteurSuiviDemandes(User $directeur)
    {
        $requete = $this->_em->createQueryBuilder();
    
        $requete
        ->select(
            'interventionDemande.id AS id',
            'referent.nom AS referentNom',
            'referent.prenom AS referentPrenom',
            'referentEtablissement.nom AS referentEtablissementNom',
            'referentEtablissement.finess AS referentEtablissementFiness',
            'referentRegion.libelle AS referentRegionLibelle',
            'CONCAT(\'<strong>\', ambassadeur.nom, \' \', ambassadeur.prenom, \'</strong><br>\', ambassadeurEtablissement.nom, \' - \', ambassadeurEtablissement.finess, \' (\', ambassadeurRegion.libelle, \')\') AS ambassadeurInformations',
            'interventionInitiateur.type AS interventionInitiateurType',
            'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
            'interventionEtat.libelle AS interventionEtatLibelle',
            'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
            'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
            'evaluationEtat.id AS evaluationEtatId',
            'remboursementEtat.libelle AS remboursementEtatLibelle'
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
        // État de l'évaluation
        ->leftJoin('interventionDemande.evaluationEtat', 'evaluationEtat')
        // État du remboursement
        ->leftJoin('interventionDemande.remboursementEtat', 'remboursementEtat')
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
    /**
     * Récupère les données du grid des demandes d'intervention pour l'ambassadeur sous forme de tableau correctement formaté
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur des demandes d'intervention
     * @return array
     */
    public function getGridDonnees_AmbassadeurDemandes(User $ambassadeur)
    {
        $requeteDemandesGroupees = $this->_em->createQueryBuilder()
            ->select('IDENTITY(interventionRegroupementIgnore.interventionDemandeRegroupee)')
            ->from('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupementIgnore')
            ;
        
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select(
                'interventionDemande.id AS id',
                'referent.nom AS referentNom',
                'referent.prenom AS referentPrenom',
                'referentEtablissement.nom AS referentEtablissementNom',
                'referentEtablissement.finess AS referentEtablissementFiness',
                'referentRegion.libelle AS referentRegionLibelle',
                'interventionInitiateur.type AS interventionInitiateurType',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.cmsiDateChoix, \'\') AS cmsiDateChoixLibelle',
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'remboursementEtat.libelle AS remboursementEtatLibelle',
                'COUNT(interventionRegroupement) AS nombreRegroupements'
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
                // Regroupement
                ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement', Join::WITH, 'interventionDemande.id = interventionRegroupement.interventionDemandePrincipale')
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
    public function getGridDonnees_EtablissementDemandes(User $referent)
    {
        // Ignorer les demandes groupées pour un même référence
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
                'CONCAT(interventionDemande.ambassadeurDateChoix, \'\') AS ambassadeurDateChoixLibelle',
                'evaluationEtat.id AS evaluationEtatId',
                'COUNT(interventionRegroupement) AS nombreRegroupements'
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
            // Regroupement
            ->leftJoin('HopitalNumeriqueInterventionBundle:InterventionRegroupement', 'interventionRegroupement', Join::WITH, 'interventionDemande.id = interventionRegroupement.interventionDemandePrincipale');

            $requete->where('interventionDemande.referent = :referent')
                ->setParameter('referent', $referent)
                ->andWhere(
                    $requete->expr()->notIn(
                        'interventionDemande',
                        $requeteDemandesGroupees->getDQL()
                    )
                );

        $requete->orderBy('interventionDemande.dateCreation', 'DESC')
            ->groupBy('interventionDemande.id');

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
        
        //foreach ($requete->getQuery()->getResult() as $resultat)
        //    echo $resultat->getId().'<br>';
        
        return $requete->getQuery()->getResult();
    }
}
