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
            ->andWhere('con.archiver = 0', 
                $qb->expr()->between(
                    'con.dateRenouvellement',
                    $today->getTimestamp(),
                    $in45Days->getTimestamp()
                )
            );
        
        return $qb;
    }
}