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
     * @param ObjectIdentity[] $objectsIdentity
     */
    public function populateMultiple(array $objectsIdentity)
    {
        $grouped = [];
        foreach ($objectsIdentity as $objectIdentity) {
            if (!isset($grouped[$objectIdentity->getClass()])) {
                $grouped[$objectIdentity->getClass()] = [];
            }

            $grouped[$objectIdentity->getClass()][$objectIdentity->getId()] = $objectIdentity->getObjectId();
        }

        foreach ($grouped as $class => $ids) {
            foreach ($this->_em->getRepository($class)->findById($ids) as $object) {
                $objectsIdentity[array_search($object->getId(), $ids)]->setObject($object);
            }
        }
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return ObjectIdentity[]
     */
    public function getRelatedObjects(ObjectIdentity $objectIdentity)
    {
        /** @var ObjectIdentity[] $objects */
        $objects = $this->createQueryBuilder('object', 'object.id')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.targetObjectIdentity = object.id')
            ->join('relation.sourceObjectIdentity', 'source', Join::WITH, 'source.id = :source')
            ->setParameter('source', $objectIdentity)

            ->addOrderBy('relation.order')

            ->getQuery()->getResult()
        ;

        $this->populateMultiple($objects);

        return $objects;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return ObjectIdentity[]
     */
    public function getRelatedByObjects(ObjectIdentity $objectIdentity)
    {
        /** @var ObjectIdentity[] $objects */
        $objects = $this->createQueryBuilder('object', 'object.id')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.sourceObjectIdentity = object.id')
            ->join('relation.targetObjectIdentity', 'target', Join::WITH, 'target.id = :target')
            ->setParameter('target', $objectIdentity)

            ->addOrderBy('relation.order')

            ->getQuery()->getResult()
        ;

        $this->populateMultiple($objects);

        return $objects;
    }
}
