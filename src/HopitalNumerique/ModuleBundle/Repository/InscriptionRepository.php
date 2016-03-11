<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

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
    public function getAllDatasForGrid( $domainesIds, $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ins')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'ins')
            ->leftJoin('ins.session','ses')
                ->leftJoin('ses.module', 'mod')
                    ->leftJoin('mod.domaines', 'domaine')
                        ->andWhere($qb->expr()->orX(
                            $qb->expr()->in('domaine.id', ':domainesId'),
                            $qb->expr()->isNull('domaine.id')
                        ))
                    ->setParameter('domainesId', $domainesIds)
            ->leftJoin('ses.etat','refEtat')
                ->andWhere('refEtat.id = 403')
            ->leftJoin('ins.user','user')
            ->groupBy('ins.id', 'domaine.id')
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
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('insc')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
            ->leftJoin('insc.etatEvaluation', 'refEvaluation')
            ->leftJoin('insc.etatParticipation', 'refParticipation')
            ->leftJoin('insc.session', 'session')
            ->andWhere($qb->expr()->orX('insc.etatRemboursement != 6', $qb->expr()->isNull('insc.etatRemboursement')))
            ->andWhere('refParticipation.id = 411','insc.facture IS NULL')
            ->andWhere('refEvaluation.id = 29')
            ->orderBy('session.dateSession')
        ;

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
                         ->orderBy('session.dateSession', 'DESC');
    }
    
    /**
     * Retourne la date de début de session de la première inscription de chaque utilisateur pour chaque module.
     */
    public function getInscriptionsByUser( $usersId ){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id as userId,
                     session.dateSession as date,
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

    /**
     * Retourne le nombre d'inscriptions pour l'année.
     *
     * @param integer $annee Année
     * @return integer Total
     */
    public function getCountForYear($annee, Domaine $domaine)
    {
        $queryBuilder = $this->createQueryBuilder('inscription');
        $anneeCourantePremierJour = new \DateTime();
        $anneeCourantePremierJour->setDate($annee, 1, 1);
        $anneeCourantePremierJour->setTime(0, 0, 0);
        $anneeSuivanteDernierJour = new \DateTime();
        $anneeSuivanteDernierJour->setDate($annee + 1, 1, 1);
        $anneeSuivanteDernierJour->setTime(0, 0, 0);

        $queryBuilder
            ->select('COUNT(DISTINCT(inscription.id)) AS total')
            ->innerJoin('inscription.session', 'session')
            ->innerJoin('session.module', 'module')
            ->innerJoin('module.domaines', 'domaine', Expr\Join::WITH, $queryBuilder->expr()->eq('domaine.id', ':domaine'))
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('inscription.dateInscription', ':anneeCourantePremierJour'),
                $queryBuilder->expr()->lt('inscription.dateInscription', ':anneeSuivanteDernierJour')
            ))
            ->andWhere($queryBuilder->expr()->eq('inscription.etatInscription', ':etatInscriptionAccepte'))
            ->andWhere($queryBuilder->expr()->eq('inscription.etatParticipation', ':etatParticipationParticipe'))
            ->setParameters(array(
                'domaine' => $domaine,
                'etatInscriptionAccepte' => 407, // Accepté
                'etatParticipationParticipe' => 411, // A participé
                'anneeCourantePremierJour' => $anneeCourantePremierJour,
                'anneeSuivanteDernierJour' => $anneeSuivanteDernierJour
            ))
        ;

        return intval($queryBuilder->getQuery()->getSingleScalarResult());
    }

    /**
     * Retourne le nombre d'utilisateurs uniques inscrits pour l'année.
     *
     * @param integer $annee Année
     * @return integer Total
     */
    public function getUsersCountForYear($annee, Domaine $domaine)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $anneeCourantePremierJour = new \DateTime();
        $anneeCourantePremierJour->setDate($annee, 1, 1);
        $anneeCourantePremierJour->setTime(0, 0, 0);
        $anneeSuivanteDernierJour = new \DateTime();
        $anneeSuivanteDernierJour->setDate($annee + 1, 1, 1);
        $anneeSuivanteDernierJour->setTime(0, 0, 0);

        $queryBuilder
            ->select('COUNT(DISTINCT(user.id)) AS total')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->innerJoin('user.inscriptions', 'inscription', Expr\Join::WITH, $queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('inscription.dateInscription', ':anneeCourantePremierJour'),
                $queryBuilder->expr()->lt('inscription.dateInscription', ':anneeSuivanteDernierJour')
            ))
            ->innerJoin('inscription.session', 'session')
            ->innerJoin('session.module', 'module')
            ->innerJoin('module.domaines', 'domaine', Expr\Join::WITH, $queryBuilder->expr()->eq('domaine.id', ':domaine'))
            ->where($queryBuilder->expr()->eq('inscription.etatInscription', ':etatInscriptionAccepte'))
            ->andWhere($queryBuilder->expr()->eq('inscription.etatParticipation', ':etatParticipationParticipe'))
            ->setParameters(array(
                'domaine' => $domaine,
                'etatInscriptionAccepte' => 407, // Accepté
                'etatParticipationParticipe' => 411, // A participé
                'anneeCourantePremierJour' => $anneeCourantePremierJour,
                'anneeSuivanteDernierJour' => $anneeSuivanteDernierJour
            ))
        ;

        return intval($queryBuilder->getQuery()->getSingleScalarResult());
    }
}
