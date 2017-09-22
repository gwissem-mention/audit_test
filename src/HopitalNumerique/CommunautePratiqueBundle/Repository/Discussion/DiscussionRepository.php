<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;

/**
 * Class DiscussionRepository
 */
class DiscussionRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param DiscussionListQuery $query
     *
     * @return Discussion[]
     */
    public function getPublicDiscussionsForDomains(DiscussionListQuery $query)
    {
        $querybuilder = $this->createQueryBuilder('discussion')
            ->join('discussion.messages', 'message')
            ->addSelect('message')
            ->join('message.user', 'messageUser')
            ->addSelect('messageUser')
            ->leftJoin('discussion.groups', 'cdpGroup')
            ->addSelect('cdpGroup')
        ;

        if (null !== $query->user) {
            $querybuilder
                ->leftJoin('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
                ->setParameter('user', $query->user)
            ;
        }

        if (!$query->displayAll) {
            $querybuilder->orWhere('message.published = TRUE');
        }

        if (count($query->displayAllForGroups)) {
            $querybuilder
                ->orWhere('cdpGroup IN (:displayAllForGroups)')
                ->setParameter('displayAllForGroups', $query->displayAllForGroups)
            ;
        }

        $querybuilder
            ->addOrderBy('message.createdAt', 'DESC')
        ;

        return $querybuilder
            ->getQuery()->getResult()
        ;
    }
}
