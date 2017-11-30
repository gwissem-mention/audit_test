<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Member;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Member\ViewedMember;
use HopitalNumerique\UserBundle\Entity\User;

class ViewedMemberRepository extends EntityRepository
{
    /**
     * @param User $viewer
     * @param User[] $members
     *
     * @return ViewedMember[]
     */
    public function findByViewer(User $viewer, array $members = [])
    {

        $queryBuilder = $this->createQueryBuilder('view')
            ->join('view.viewer', 'viewer', Join::WITH, 'view.viewer = :user')
            ->addSelect('viewer')
            ->setParameter('user', $viewer)
        ;

        if (count($members)) {
            $queryBuilder
                ->addSelect('cdp_member')
                ->join('view.member', 'cdp_member', Join::WITH, 'cdp_member.id IN (:members)')
                ->setParameter('members', $members)
            ;
        }

        /** @var ViewedMember[] $views */
        $views = $queryBuilder->getQuery()->getResult();

        $sortedViews = [];
        foreach ($views as $view) {
            $sortedViews[$view->getMember()->getId()] = $view;
        }

        return $sortedViews;
    }
}
