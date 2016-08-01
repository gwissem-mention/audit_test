<?php
namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
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
                'attribute_id' => $attribute->getId()
            ]);

        return $qb->getQuery()->getResult();
    }
}
