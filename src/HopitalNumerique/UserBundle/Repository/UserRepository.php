<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid()
    {        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id, 
                        user.username, 
                        user.email, 
                        user.nom, 
                        user.prenom, 
                        refRegion.libelle as region, 
                        refRole.name as roles , 
                        refEtat.libelle as etat, 
                        user.lock, 
                        min(contractualisation.dateRenouvellement) as contra
            ')
//                         questionnaire.id as questionnaireId
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.etat','refEtat')
            ->leftJoin('user.region','refRegion')
            ->leftJoin('user.roles','refRole')
            ->leftJoin('user.contractualisations', 'contractualisation', 'WITH', 'contractualisation.archiver = 0')
            //Récupération des réponses par questionnaire pour vérifier si ils sont remplis
//             ->leftJoin('user.reponses', 'reponse')
//             ->leftJoin('reponse.question', 'question')
//             ->leftJoin('question.questionnaire' , 'questionnaire')
            ->groupBy('user')
            ->orderBy('user.username');
        
        return $qb;
    }

    /**
     * Retourne la liste des établissement 'Autres'
     *
     * @return QueryBuilder
     */
    public function getDatasForGridEtablissement()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id, user.username, user.nom, user.prenom, refRegion.libelle as region, user.archiver, user.autreStructureRattacheementSante')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.region','refRegion')
            ->where('user.autreStructureRattacheementSante IS NOT NULL ')
            ->orderBy('user.username');
        
        return $qb;
    }
}