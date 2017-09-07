<?php

namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Category;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;

class ContainerRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getWithJoinsQueryBuilder()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.autodiag', 'a')->addSelect('a')
            ->leftJoin('a.domaines', 'd')->addSelect('d')
            ->leftJoin('a.presets', 'ap')->addSelect('ap')
        ;
    }

    /**
     * @param integer $containerId
     *
     * @return Container
     */
    public function findByIdWithJoin($containerId)
    {
        return $this->getWithJoinsQueryBuilder()
            ->andWhere('c.id = :containerId')->setParameter('containerId', $containerId)

            ->setMaxResults(1)
            ->getQuery()->getSingleResult()
        ;
    }

    /**
     * @param array $containerIds
     *
     * @return Container[]
     */
    public function findByIdsWithJoin($containerIds)
    {
        return $this->getWithJoinsQueryBuilder()
            ->andWhere('c.id IN (:containerIds)')->setParameter('containerIds', $containerIds)

            ->getQuery()->getResult()
        ;
    }

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

    /**
     * @return Chapter[]|null
     */
    public function getChapters()
    {
        $qb = $this->createQueryBuilder('container');
        $qb
            ->where(
                $qb->expr()->isInstanceOf('container', Chapter::class)
            );

        return $qb->getQuery()->getResult();
    }
}
