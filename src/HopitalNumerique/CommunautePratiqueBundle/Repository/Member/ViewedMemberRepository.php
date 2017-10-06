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
     *
     * @return ViewedMember[]
     */
    public function findByViewer(User $viewer)
    {
        /** @var ViewedMember[] $views */
        $views = $this->createQueryBuilder('view')
            ->join('view.member', 'cdp_member')
            ->addSelect('cdp_member')
            ->join('view.viewer', 'viewer', Join::WITH, 'view.viewer = :user')
            ->addSelect('viewer')
            ->setParameter('user', $viewer)
            ->getQuery()->getResult()
        ;

        $sortedViews = [];
        foreach ($views as $view) {
            $sortedViews[$view->getMember()->getId()] = $view;
        }

        return $sortedViews;
    }
}
