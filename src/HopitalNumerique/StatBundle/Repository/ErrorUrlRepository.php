<?php

namespace HopitalNumerique\StatBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Class ErrorUrlRepository.
 */
class ErrorUrlRepository extends EntityRepository
{
    public function truncate()
    {
        $em = $this->getEntityManager();
        $em->createQuery("DELETE FROM 'HopitalNumerique\StatBundle\Entity\ErrorUrl'")->execute();

        return true;
    }

    /**
     * Get number of errors by domain
     *
     * @param $domains
     *
     * @return mixed
     */
    public function nbErrorsByDomain($domains)
    {
        return $this->createQueryBuilder('e')
            ->select('count(e) as nbErrorsUrl')
            ->join('e.domain', 'domain', Join::WITH, 'domain.id IN (:domains)')
            ->setParameter('domains', $domains)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
