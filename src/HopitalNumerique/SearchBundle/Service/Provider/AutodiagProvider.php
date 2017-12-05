<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;

/**
 * Autodiag type provider
 *
 */
class AutodiagProvider extends AbstractProvider
{

    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $qb = $this->repository->createQueryBuilder('autodiag');
        $qb
            ->join('autodiag.domaines', 'domaine', Join::WITH, 'domaine.slug = :domaineSlug')
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        return $qb;
    }
}
