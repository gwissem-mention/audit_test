<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * InscriptionRepository
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('insc.id, 
                     user.id as userId,
                     user.nom as userNom,
                     user.prenom as userPrenom, 
                     user.roles,
                     session.id as sessionId,
                     refProfilEtablissementSante.libelle as userProfil,
                     refRegion.libelle as userRegion,
                     insc.commentaire,
                     refEtatInscription.libelle as etatInscription,
                     refEtatParticipation.libelle as etatParticipation,
                     refEtatEvaluation.libelle as etatEvaluation')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
            ->leftJoin('insc.user','user')
            ->leftJoin('user.region','refRegion')
            ->leftJoin('user.profilEtablissementSante','refProfilEtablissementSante')
            ->leftJoin('insc.etatInscription','refEtatInscription')
            ->leftJoin('insc.etatParticipation','refEtatParticipation')
            ->leftJoin('insc.etatEvaluation','refEtatEvaluation')
            ->leftJoin('insc.session','session')
            ->where( 'session.id = :idSession' )
            ->setParameter('idSession', $condition->value )
            ->orderBy('user.nom');
    
        return $qb;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllDatasForGrid( $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ins')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'ins')
            ->leftJoin('ins.session','ses')
            ->leftJoin('ses.etat','refEtat')
            ->andWhere('refEtat.id = 403')
            ->leftJoin('ins.user','user')
            // ->leftJoin('ses.module','module')
            ->groupBy('ins.id')
            ->orderBy('user.nom');

        return $qb;
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur pour la création des factures
     *
     * @return QueryBuilder
     */
    public function getForFactures( $user = null )
    {
        $qb = $this->_em->createQueryBuilder()
                         ->select('insc')
                         ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
                         ->leftJoin('insc.etatRemboursement', 'refRemboursement')
                         ->leftJoin('insc.etatEvaluation', 'refEvaluation')
                         ->leftJoin('insc.etatParticipation', 'refParticipation')
                         ->leftJoin('insc.session', 'session')
                         ->andWhere('refParticipation.id = 411','insc.facture IS NULL')
                         ->andWhere('refEvaluation.id = 29')
                         ->orderBy('session.dateSession');

        if( !is_null($user) ) {
            $qb->andWhere('insc.user = :user')
               ->setParameter('user', $user);
        }

        return $qb;
    }

    /**
     * Retourne la liste des inscriptions d'une facture ordonnée par dateSession
     *
     * @return QueryBuilder
     */
    public function getInscriptionsForFactureOrdered( $facture )
    {
        return $this->_em->createQueryBuilder()
                         ->select('insc')
                         ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
                         ->leftJoin('insc.session','session')
                         ->leftJoin('insc.facture','facture')
                         ->andWhere('facture.id = :factureId')
                         ->setParameter('factureId', $facture)
                         ->orderBy('session.dateSession', 'ASC');
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur
     *
     * @return QueryBuilder
     */
    public function getInscriptionsForUser( $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('insc')
                         ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
                         ->leftJoin('insc.session','session')
                         ->leftJoin('insc.etatInscription','refEtatInscription')
                         ->andWhere('insc.user = :user', 'refEtatInscription.id != 409')
                         ->setParameter('user', $user)
                         ->orderBy('session.dateSession', 'ASC');
    }
    
    /*
     * Retourne la date de la première inscription de chaques utilisateurs pour chaque module
     */
    public function getInscriptionsByUser( $usersId ){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id as userId,
                     insc.dateInscription as date,
                     session.id as sessionId,
                     module.id as moduleId'
            )
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
            ->leftJoin('insc.user','user')
            ->leftJoin('insc.session','session')
            ->leftJoin('session.module','module')
            ->leftJoin('insc.etatParticipation','etat')
            ->where( 'user.id IN (:users)' )
            ->andWhere( 'etat.id = 411' )
            ->setParameter('users', $usersId )
            ->orderBy('insc.dateInscription')
            ->groupBy('user.id, module.id');
        
        return $qb;
    }
}
