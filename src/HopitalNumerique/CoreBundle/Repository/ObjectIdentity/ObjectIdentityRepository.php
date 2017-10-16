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
    public function getRelatedObjects(ObjectIdentity $objectIdentity, $targetClass = null)
    {
        $queryBuilder = $this->createQueryBuilder('object', 'object.id')
            ->join(Relation::class, 'relation', Join::WITH, 'relation.targetObjectIdentity = object.id')
            ->join('relation.sourceObjectIdentity', 'source', Join::WITH, 'source.id = :source')
            ->setParameter('source', $objectIdentity)

            ->addOrderBy('relation.order')
        ;

        if (null !== $targetClass) {
            $queryBuilder
                ->join('relation.targetObjectIdentity', 'target', Join::WITH, 'target.class = :targetClass')
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
}
