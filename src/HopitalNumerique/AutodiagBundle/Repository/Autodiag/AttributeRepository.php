<?php
namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

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
                'attribute_id' => $attribute->getId()
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
                'autodiag_id' => $autodiag->getId()
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
                'autodiag_id' => $autodiag->getId()
            ]);

        $result = $qb->getQuery()->getArrayResult();
        $data = [];
        foreach ($result as $count) {
            $data[$count['container_id']] = $count['total'];
        }

        return $data;
    }
}
