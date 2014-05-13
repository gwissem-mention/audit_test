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
    public function getDatasForGrid( $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('insc.id, 
                     user.id as userId,
                     user.nom as userNom,
                     user.prenom as userPrenom, 
                     user.roles,
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
     * Retourne la liste des inscriptions de l'utilisateur
     *
     * @return QueryBuilder
     */
    public function getForFactures( $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('insc')
                         ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
                         ->leftJoin('insc.etatRemboursement', 'refRemboursement')
                         ->leftJoin('insc.etatEvaluation', 'refEvaluation')
                         ->leftJoin('insc.etatParticipation', 'refParticipation')
                         ->andWhere('refRemboursement.id = 5', 'refEvaluation.id = 29')
                         ->andWhere('refParticipation.id = 411','insc.facture IS NULL')
                         ->andWhere('insc.user = :user')
                         ->setParameter('user', $user);
    }
}
