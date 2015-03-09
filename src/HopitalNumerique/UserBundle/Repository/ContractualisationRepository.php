<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ContractualisationRepository
 */
class ContractualisationRepository extends EntityRepository
{
    /**
     * Récupère le nombre de contractualisation à renouveler depuis 45jours
     *
     * @return qb
     */
    public function getContractualisationsARenouveler()
    {        
        $today    = new \DateTime();
        $in45Days = new \DateTime();
        $in45Days->modify('+ 45 days');

        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(con)')
            ->from('HopitalNumeriqueUserBundle:Contractualisation', 'con')
            ->andWhere('con.archiver = 0', 'con.dateRenouvellement BETWEEN :today AND :in45Days')
            ->setParameter('today', $today->format('Y-m-d') )
            ->setParameter('in45Days', $in45Days->format('Y-m-d') );
        
        return $qb;
    }
    
    /**
     * Récupère les contractualisations pour un utilisateur donné
     *
     * @return qb
     */
    public function getContractualisationForGrid( $condition = null )
    {        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('contract.id,
            contract.nomDocument,
            contract.dateRenouvellement,
            contract.archiver,
            contract.path,
            typeDocument.libelle
            ')
            ->from('HopitalNumeriqueUserBundle:Contractualisation', 'contract')
            ->innerJoin('contract.user', 'user')
            ->innerJoin('contract.typeDocument', 'typeDocument')
            ->addOrderBy('user.username');
        
        if( $condition )
        {
            $qb->where('user.id = :id')
                    ->setParameter('id', $condition->value);
        }
        
        return $qb;
    }
}