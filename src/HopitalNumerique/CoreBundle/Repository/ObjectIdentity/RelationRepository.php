<?php

namespace HopitalNumerique\CoreBundle\Repository\ObjectIdentity;

use Doctrine\ORM\EntityRepository;
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
}
