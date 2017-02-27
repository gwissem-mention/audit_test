<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;

class ObjectProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('object');
        $queryBuilder
            ->join('object.domaines', 'domaine', Join::WITH, 'domaine.slug = :domaineSlug')
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        return $queryBuilder;
    }
}
