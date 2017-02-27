<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;

/**
 * Content type Provider
 */
class ContentProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('content');
        $queryBuilder
            ->join('content.objet', 'objet')
            ->join('objet.domaines', 'domaine', Join::WITH, 'domaine.slug = :domaineSlug')
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        return $queryBuilder;
    }
}
