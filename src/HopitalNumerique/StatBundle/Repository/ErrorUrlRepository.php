<?php

namespace HopitalNumerique\StatBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
}
