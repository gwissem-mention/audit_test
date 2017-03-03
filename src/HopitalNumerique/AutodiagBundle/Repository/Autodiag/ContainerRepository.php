<?php

namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Category;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;

class ContainerRepository extends EntityRepository
{
    /**
     * @param $attributes
     *
     * @return Chapter[]|Category[]
     */
    public function getConcernedByAttributes($attributes)
    {
        $qb = $this->createQueryBuilder('container');
        $qb
            ->join('container.attributesWeighted', 'weight')
            ->join('weight.attribute', 'attribute')
            ->where(
                $qb->expr()->in('attribute.id', array_map(function (Attribute $attribute) {
                    return $attribute->getId();
                }, $attributes))
            );

        return $qb->getQuery()->getResult();
    }
}
