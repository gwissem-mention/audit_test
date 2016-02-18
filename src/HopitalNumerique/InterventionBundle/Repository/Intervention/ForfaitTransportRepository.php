<?php
namespace HopitalNumerique\InterventionBundle\Repository\Intervention;

use Doctrine\ORM\EntityRepository;

/**
 * ForfaitTransportRepository.
 */
class ForfaitTransportRepository extends EntityRepository
{
    /**
     * Retourne le ForfaitTransport d'une distance en km.
     *
     * @param integer $distance Distance
     * @return \HopitalNumerique\InterventionBundle\Entity\Intervention\ForfaitTransport|null ForfaitTransport correspondant
     */
    public function getForDistance($distance)
    {
        $queryBuilder = $this->createQueryBuilder('forfaitTransport');

        $queryBuilder
            ->where($queryBuilder->expr()->gt('forfaitTransport.cout', $distance))
            ->orderBy('forfaitTransport.cout', 'ASC')
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
