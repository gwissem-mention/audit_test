<?php

namespace HopitalNumerique\StatBundle\Repository;

use Doctrine\ORM\EntityRepository;
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
     * @param Domaine $domain
     *
     * @return mixed
     */
    public function nbErrorsByDomain(Domaine $domain)
    {
        return $this->createQueryBuilder('e')
            ->select('count(e) as nbErrorsUrl')
            ->where('e.domain = :domain')
            ->setParameter('domain', $domain)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
