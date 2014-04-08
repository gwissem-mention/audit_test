<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ContractualisationRepository
 */
class ContractualisationRepository extends EntityRepository
{
    // /**
    //  * Récupère les données du grid sous forme de tableau correctement formaté
    //  *
    //  * @return array
    //  */
    // public function getDatasForGrid( $condition )
    // {         
    //     $qb = $this->_em->createQueryBuilder();
    //     $qb->select('contra.id, contra.nomDocument, contra.dateRenouvellement, refTypeDocument.libelle as typeDocument, contra.archiver')
    //         ->from('HopitalNumeriqueUserBundle:User', 'user')
    //         ->leftJoin('user.contractualisations', 'contra')
    //         ->leftJoin('contra.typeDocument','refTypeDocument')
    //         ->where( 'user.id = :idUser')
    //         ->setParameter('idUser', $condition->value )
    //         ->orderBy('contra.dateRenouvellement');
        
    //     return $qb->getQuery()->getResult();
    // }    
    
}