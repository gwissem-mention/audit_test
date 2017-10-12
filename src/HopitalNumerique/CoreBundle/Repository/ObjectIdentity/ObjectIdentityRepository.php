<?php

namespace HopitalNumerique\CoreBundle\Repository\ObjectIdentity;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\Relation;

class ObjectIdentityRepository extends EntityRepository
{
    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return null|object
     */
    public function populate(ObjectIdentity $objectIdentity)
    {
        return $this->_em->getRepository($objectIdentity->getClass())->find($objectIdentity->getObjectId());
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return ObjectIdentity[]
     */
    public function getRelatedObjects(ObjectIdentity $objectIdentity)
    {
        /** @var ObjectIdentity[] $objects */
        $objects = $this->createQueryBuilder('object')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.targetObjectIdentity = object.id')
            ->join('relation.sourceObjectIdentity', 'source', Join::WITH, 'source.id = :source')
            ->setParameter('source', $objectIdentity)

            ->addOrderBy('relation.order')

            ->getQuery()->getResult()
        ;

        foreach ($objects as $object) {
            $object->setObject($this->populate($object));
        }

        return $objects;
    }
}
