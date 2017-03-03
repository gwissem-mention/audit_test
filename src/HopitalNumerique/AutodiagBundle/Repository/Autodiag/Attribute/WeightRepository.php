<?php

namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag\Attribute;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;

class WeightRepository extends EntityRepository
{
    public function getWeightByContainerIndexedByAttributeId(Container $container)
    {
        $qb = $this->createQueryBuilder('weight');
        $qb
            ->where('weight.container = :container')
            ->setParameters([
                'container' => $container->getId(),
            ]);

        $results = $qb->getQuery()->getResult();
        $data = [];

        foreach ($results as $result) {
            $data[$result->getAttribute()->getId()] = $result->getWeight();
        }

        return $data;
    }
}
