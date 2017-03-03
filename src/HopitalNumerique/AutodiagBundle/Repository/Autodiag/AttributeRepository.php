<?php

namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;

class AttributeRepository extends EntityRepository
{
    public function getAttributeContainersWeight(Attribute $attribute)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('weight', 'container')
            ->from('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight', 'weight')
            ->join('weight.container', 'container')
            ->join('weight.attribute', 'attribute')
            ->where('attribute.id = :attribute_id')
            ->setParameters([
                'attribute_id' => $attribute->getId(),
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getAttributesHavingChapter(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('attribute');
        $qb
            ->join('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight', 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container', Join::WITH, $qb->expr()->isInstanceOf('container', Autodiag\Container\Chapter::class))
            ->groupBy('attribute.id')
            ->where('attribute.autodiag = :autodiag_id')
            ->setParameters([
                'autodiag_id' => $autodiag->getId(),
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function countForAutodiag(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('attribute');
        $qb
            ->select('count(attribute.id) as total', 'container.id as container_id')
            ->join(Attribute\Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container')
            ->where('container.autodiag = :autodiag_id')
            ->groupBy('container.id')
            ->setParameters([
                'autodiag_id' => $autodiag->getId(),
            ]);

        $result = $qb->getQuery()->getArrayResult();
        $data = [];
        foreach ($result as $count) {
            $data[$count['container_id']] = $count['total'];
        }

        return $data;
    }

    public function getAutodiagAttributesGroupedByContainer(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('attribute');
        $qb
            ->select('attribute as att', 'options', 'group_concat(distinct container.id) as container_id')
            ->join(Attribute\Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container')
            ->leftJoin('attribute.options', 'options')
            ->where('attribute.autodiag = :autodiag_id')
            ->groupBy('options.value')
            ->setParameters([
                'autodiag_id' => $autodiag->getId(),
            ]);

        $results = $qb->getQuery()->getResult();

        $data = [];
        foreach ($results as $result) {
            $ids = explode(',', $result['container_id']);
            foreach ($ids as $id) {
                if (!isset($data[$id])) {
                    $data[$id] = [];
                }
                $data[$id][] = $result['att'];
            }
        }

        return $data;
    }

    public function getMinAndMaxForAutodiagByAttributes(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('attribute', 'attribute.id');
        $qb
            ->select(
                'attribute.id',
                'MAX(options.value) as maximum',
                'MIN(options.value) as minimum'
            )
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value != \'-1\'')
            ->join('attribute.autodiag', 'autodiag')
            ->where('autodiag.id = :autodiag_id')
            ->groupBy('attribute.id')
            ->setParameters([
                'autodiag_id' => $autodiag->getId(),
            ]);

        return $qb->getQuery()->getArrayResult();
    }

    public function getAttributesWithChapter(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('attribute', 'attribute.id');
        $qb
            ->select(
                'attribute.id',
                'attribute.label as attribute_label',
                'parent.label as chapter_parent',
                'container.label as chapter',
                'weight.weight'
            )
            ->join(Attribute\Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container', Join::WITH, $qb->expr()->isInstanceOf('container', Autodiag\Container\Chapter::class))
            ->leftJoin('container.parent', 'parent')
            ->join('attribute.autodiag', 'autodiag')
            ->where('autodiag.id = :autodiag_id')
            ->orderBy('attribute.order', 'ASC')
            ->setParameters([
                'autodiag_id' => $autodiag->getId(),
            ]);

        return $qb->getQuery()->getArrayResult();
    }
}
