<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Item\CartItem;

class CartItemRepository extends EntityRepository
{
    /**
     * @param $objectType
     * @param $objectId
     * @param User $owner
     *
     * @return CartItem|null
     */
    public function findByObjectAndOwner($objectType, $objectId, User $owner)
    {
        return $this->createQueryBuilder('ci')
            ->andWhere('ci.objectType = :objectType')->setParameter('objectType', $objectType)
            ->andWhere('ci.objectId = :objectId')->setParameter('objectId', $objectId)
            ->andWhere('ci.owner = :ownerId')->setParameter('ownerId', $owner->getId())

            ->getQuery()->getOneOrNullResult()
        ;
    }
}
