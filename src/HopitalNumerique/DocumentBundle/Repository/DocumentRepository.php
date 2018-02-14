<?php

namespace HopitalNumerique\DocumentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class DocumentRepository
 */
class DocumentRepository extends EntityRepository
{
    public function getDocumentsByUserAndType(User $user, $type)
    {
        return $this->createQueryBuilder('document')
            ->where('document.user = :user')
            ->andWhere('document.type = :type')
            ->setParameters([
                'user' => $user,
                'type' => $type,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
