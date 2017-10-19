<?php

namespace HopitalNumerique\CoreBundle\Repository\ObjectIdentity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\Relation;

class RelationRepository extends EntityRepository
{
    /**
     * @param ObjectIdentity $source
     * @param ObjectIdentity $target
     *
     * @return Relation
     */
    public function addRelation(ObjectIdentity $source, ObjectIdentity $target)
    {
        /** @var Relation $relation */
        if ($relation = $this->findOneBy(['sourceObjectIdentity' => $source, 'targetObjectIdentity' => $target])) {
            return $relation;
        }

        $relation = new Relation($source, $target);

        $this->_em->persist($relation);

        return $relation;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     */
    public function removeFrom(ObjectIdentity $objectIdentity, $except = [])
    {
        /** @var Relation $single */
        foreach ($this->findBySourceObjectIdentity($objectIdentity) as $single) {
            if (in_array($single->getTargetObjectIdentity()->getId(), $except)) {
                continue;
            }

            $this->_em->remove($single);
        }
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @return array
     */
    public function getObjectIdentityRelations(ObjectIdentity $objectIdentity)
    {
        return $this->createQueryBuilder('relation')
            ->join('relation.sourceObjectIdentity', 'source', Join::WITH, 'source.id = :source')
            ->setParameter('source', $objectIdentity->getId())

            ->join('relation.targetObjectIdentity', 'target')
            ->addSelect('target')

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return array
     */
    public function getObjectIdentityRelatedByRelations(ObjectIdentity $objectIdentity)
    {
        return $this->createQueryBuilder('relation')
            ->join('relation.sourceObjectIdentity', 'source')

            ->join('relation.targetObjectIdentity', 'target', Join::WITH, 'target.id = :target')
            ->setParameter('target', $objectIdentity->getId())
            ->addSelect('target')

            ->getQuery()->getResult()
        ;
    }
}
