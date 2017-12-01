<?php

namespace HopitalNumerique\CoreBundle\Repository\ObjectIdentity;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\Relation;

class ObjectIdentityRepository extends EntityRepository
{
    /**
     * Return the referenced object corresponding to the ObjectIdentity
     *
     * @param ObjectIdentity $objectIdentity
     *
     * @return object
     * @throws NoResultException
     */
    public function populate(ObjectIdentity $objectIdentity)
    {
        if ($object = $this->_em->getRepository($objectIdentity->getClass())->find($objectIdentity->getObjectId())) {
            return $object;
        }

        throw new NoResultException();
    }

    /**
     * Return the referenced objects corresponding to the ObjectIdentity list
     *
     * @param ObjectIdentity[] $objectsIdentity
     */
    public function populateMultiple(array &$objectsIdentity)
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

        foreach ($objectsIdentity as $key => $objectIdentity) {
            if (null === $objectIdentity->getObject()) {
                $this->_em->remove($objectIdentity);
                $this->_em->flush($objectIdentity);
                unset($objectsIdentity[$key]);
            }
        }
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @param string $targetClass
     *
     * @return ObjectIdentity[]
     */
    public function getRelatedObjects(ObjectIdentity $objectIdentity, $targetClass = [])
    {
        $queryBuilder = $this->createQueryBuilder('object', 'object.id')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.targetObjectIdentity = object.id')
            ->join('relation.sourceObjectIdentity', 'source', Join::WITH, 'source.id = :source')
            ->setParameter('source', $objectIdentity)

            ->addOrderBy('relation.order')
        ;

        if (count($targetClass)) {
            $queryBuilder
                ->join('relation.targetObjectIdentity', 'target', Join::WITH, 'target.class IN (:targetClass)')
                ->setParameter('targetClass', $targetClass)
            ;
        }

        /** @var ObjectIdentity[] $objects */
        $objects = $queryBuilder->getQuery()->getResult();

        $this->populateMultiple($objects);

        return $objects;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @param string $sourceClass
     *
     * @return ObjectIdentity[]
     */
    public function getRelatedByObjects(ObjectIdentity $objectIdentity, $sourceClass = null)
    {
        $queryBuilder = $this->createQueryBuilder('object', 'object.id')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.sourceObjectIdentity = object.id')
            ->join('relation.targetObjectIdentity', 'target', Join::WITH, 'target.id = :target')
            ->setParameter('target', $objectIdentity)

            ->addOrderBy('relation.order')
        ;

        if (null !== $sourceClass) {
            $queryBuilder
                ->join('relation.sourceObjectIdentity', 'source', Join::WITH, 'source.class = :sourceClass')
                ->setParameter('sourceClass', $sourceClass)
            ;
        }

        $objects = $queryBuilder->getQuery()->getResult();

        $this->populateMultiple($objects);

        return $objects;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @param array $acceptedClasses
     *
     * @return array
     */
    public function getBidirectionalRelationsObjects(ObjectIdentity $objectIdentity, $acceptedClasses = [])
    {
        $queryBuilder = $this->createQueryBuilder('object', 'object.id')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.sourceObjectIdentity = object.id OR relation.targetObjectIdentity = object.id')
            ->join('relation.targetObjectIdentity', 'target')
            ->join('relation.sourceObjectIdentity', 'source')
            ->andWhere('source.id = :object OR target.id = :object')
            ->andWhere('object.id != :object')
            ->setParameter('object', $objectIdentity)
            ->addOrderBy('relation.order')
            ->addGroupBy('object.id')
        ;

        if (count($acceptedClasses)) {
            $queryBuilder
                ->andWhere('source.class IN (:acceptedClasses)')
                ->andWhere('target.class IN (:acceptedClasses)')
                ->setParameter('acceptedClasses', $acceptedClasses)
            ;
        }

        $objects = $queryBuilder->getQuery()->getResult();

        $this->populateMultiple($objects);

        return $objects;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return ObjectIdentity
     */
    public function findOrCreate(ObjectIdentity $objectIdentity)
    {
        /** @var ObjectIdentity|null $persistedObjectIdentity */
        if ($persistedObjectIdentity = $this->find($objectIdentity->getId())) {
            return $persistedObjectIdentity;
        }

        $this->_em->persist($objectIdentity);

        return $objectIdentity;
    }
}
