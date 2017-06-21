<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\SearchBundle\Service\Indexable\UserIndexable;

/**
 * User type provider
 */
class UserProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('user');
        $queryBuilder
            ->join('user.domaines', 'domaine', Join::WITH, 'domaine.slug = :domaineSlug')
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        $roles = UserIndexable::getRoles();
        if (!empty($roles)) {
            $roleCondition =$queryBuilder->expr()->orX();
            foreach (UserIndexable::getRoles() as $key => $role) {
                $roleCondition->add("user.roles LIKE :role_$key");
                $queryBuilder->setParameter("role_$key", "%$role%");
            }
            $queryBuilder->andWhere($roleCondition);
        }

        return $queryBuilder;
    }
}
