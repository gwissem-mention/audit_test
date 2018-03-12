<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionDisplayQuery;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class DiscussionRepository
 */
class DiscussionRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param array $ids
     *
     * @return array
     */
    public function findByIdsIndexed(array $ids)
    {
        if (0 === count($ids)) {
            return [];
        }

        return $this->createQueryBuilder('discussion', 'discussion.id')
            ->andWhere('discussion.id IN (:ids)')
            ->setParameter('ids', $ids)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param DiscussionListQuery $query
     *
     * @return Discussion[]
     */
    public function queryForDiscussionList(DiscussionListQuery $query)
    {
        $orCondition = [];
        $queryBuilder = $this->createQueryBuilder('discussion')

            ->select('discussion')
            ->addSelect('message')
            ->addSelect('messageUser')
            ->addSelect('cdpGroup')

            ->join('discussion.messages', 'message', Join::WITH, 'message.active = TRUE')
            ->join('message.user', 'messageUser')
        ;

        if ($query->group) {
            $queryBuilder
                ->join('discussion.groups', 'cdpGroup', Join::WITH, 'cdpGroup.id = :group')
                ->setParameter('group', $query->group)
            ;
        } else {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain IN (:domains)')
                ->setParameter('domains', $query->domains)
                ->leftJoin('discussion.groups', 'cdpGroup')
                ->andWhere('discussion.public = TRUE')
            ;
        }

        if (null !== $query->user) {
            $queryBuilder
                ->leftJoin('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
                ->leftJoin('reading.user', 'reader')
                ->addSelect('reading', 'reader')
                ->setParameter('user', $query->user)
            ;

            $orCondition[] = 'message.user = :user';
        }

        if (!$query->displayAll) {
            $orCondition[] = 'message.published = TRUE';

            if (count($query->displayAllForGroups)) {
                $orCondition[] = 'cdpGroup IN (:displayAllForGroups)';
                $queryBuilder->setParameter('displayAllForGroups', $query->displayAllForGroups);
            }
        }

        if (count($orCondition) > 1) {
            $queryBuilder->andWhere(implode(' OR ', $orCondition));
        }


        $queryBuilder
            ->addOrderBy('message.createdAt', 'DESC')
            ->addGroupBy('message.id')
            ->having('COUNT(message) > 0')
        ;

        return $queryBuilder
            ->getQuery()->getResult()
        ;
    }

    /**
     * @param DiscussionDisplayQuery $query
     *
     * @return Discussion
     */
    public function queryForDiscussionDisplayQuery(DiscussionDisplayQuery $query)
    {
        $queryBuilder = $this
            ->createQueryBuilder('discussion')
            ->addSelect('message')
            ->addSelect('author')
            ->addSelect('cdpGroup')
            ->addSelect('animators')

            ->join('discussion.messages', 'message')
            ->join('message.user', 'author')
            ->leftJoin('discussion.groups', 'cdpGroup')
            ->leftJoin('cdpGroup.animateurs', 'animators')

            ->andWhere('discussion = :discussion')
            ->andWhere('message.active = TRUE')
            ->setParameter('discussion', $query->discussion)
        ;

        if ($query->group) {
            $queryBuilder
                ->andWhere('cdpGroup.id = :group')
                ->setParameter('group', $query->group)
            ;
        } else {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain IN (:domains)')
                ->setParameter('domains', $query->domains)
            ;
        }

        if (null !== $query->user) {
            $queryBuilder
                ->leftJoin('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
                ->leftJoin('reading.user', 'reader')
                ->addSelect('reading', 'reader')
                ->setParameter('user', $query->user)
            ;
        }

        if (!$query->displayAll) {
            if (null !== $query->user) {
                $queryBuilder
                    ->andWhere('message.published = TRUE OR message.user = :user')
                    ->setParameter('user', $query->user)
                ;
            } else {
                $queryBuilder->andWhere('message.published = TRUE');
            }
        }

        $queryBuilder
            ->addOrderBy('message.createdAt', 'ASC')
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Groupe $group
     * @param User $user
     *
     * @return Discussion[]
     */
    public function getDiscussionNotReaded(Groupe $group, User $user)
    {
        return $this->createQueryBuilder('discussion')
            ->join('discussion.groups', 'cdp_group', Join::WITH, 'cdp_group.id = :group')
            ->setParameter('group', $group)
            ->leftJoin('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
            ->setParameter('user', $user)

            ->andWhere('reading.id IS NULL')
            ->andWhere('discussion.active = TRUE')

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return integer
     */
    public function getPublicDiscussionCount(Domaine $domain = null)
    {
        $queryBuilder = $this->createQueryBuilder('discussion')
            ->select('count(discussion)')
            ->andWhere('discussion.public = TRUE')
            ->andWhere('discussion.active = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain->getId())
            ;
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Domaine|null $domain
     *
     * @return array
     */
    public function getPublicDiscussionForDomain(Domaine $domain)
    {
        return $this->createQueryBuilder('discussion')
            ->andWhere('discussion.public = TRUE')
            ->andWhere('discussion.active = TRUE')
            ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
            ->setParameter('domain', $domain->getId())

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     *
     * @return Discussion[]
     */
    public function getRecentPublicDiscussion(Domaine $domain = null, $limit = 20)
    {
        $queryBuilder = $this->createQueryBuilder('discussion')
            ->join('discussion.messages', 'message')->addSelect('message')
            ->join('discussion.user', 'user')
            ->andWhere('discussion.public = TRUE')
            ->andWhere('discussion.active = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder
            ->addOrderBy('discussion.createdAt', 'DESC')
            ->addOrderBy('message.createdAt', 'ASC')
            ->setMaxResults($limit)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     *
     * @return Discussion[]|Paginator
     */
    public function getRecentPublicDiscussionActivity(Domaine $domain = null, $limit = 20)
    {
        $queryBuilder = $this->createQueryBuilder('discussion')
            ->join('discussion.messages', 'message')->addSelect('message')
            ->join('discussion.user', 'user')
            ->andWhere('discussion.public = TRUE')
            ->andWhere('discussion.active = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        $queryBuilder
            ->addOrderBy('message.createdAt', 'DESC')
            ->setMaxResults($limit)
        ;

        return new Paginator($queryBuilder, true);
    }

    /**
     * @param Objet $object
     * @param Domaine|null $domain
     *
     * @return Discussion|null
     */
    public function getPublicDiscussionForObject(Objet $object, Domaine $domain = null)
    {
        $queryBuilder = $this->createQueryBuilder('discussion')
            ->join('discussion.relatedObject', 'object', Join::WITH, 'object.id = :object')
            ->setParameter('object', $object)
            ->andWhere('discussion.public = TRUE')
            ->andWhere('discussion.active = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder
            ->setMaxResults(1)

            ->getQuery()->getOneOrNullResult()
        ;
    }

    /**
     * @return Discussion[]
     */
    public function getPublicDiscussionList()
    {
        return $this->createQueryBuilder('discussion')
            ->select('discussion.title as text', 'discussion.id as value')
            ->andWhere('discussion.public = TRUE')
            ->andWhere('discussion.active = TRUE')

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return integer
     */
    public function countActiveDiscussions(array $domains)
    {
        return $this->createQueryBuilder('discussion')
            ->select('COUNT(DISTINCT discussion.id)')
            ->join('discussion.domains', 'domain', Join::WITH, 'domain.id IN (:domains)')
            ->setParameter('domains', $domains)
            ->join('discussion.messages', 'message', Join::WITH, 'message.createdAt >= :date')
            ->setParameter('date', (new \DateTime())->sub(new \DateInterval('P1M')))
            ->andWhere('discussion.active = TRUE')

            ->getQuery()->getSingleScalarResult()
        ;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return integer
     */
    public function countDiscussionWithoutReply(array $domains)
    {
        return count($this->createQueryBuilder('discussion')
            ->select('COUNT(DISTINCT discussion.id)')
            ->join('discussion.domains', 'domain', Join::WITH, 'domain.id IN (:domains)')
            ->setParameter('domains', $domains)
            ->join('discussion.messages', 'message')

            ->andWhere('discussion.active = TRUE')
            ->addGroupBy('discussion.id')
            ->andHaving('COUNT(message) = 1')

            ->getQuery()->getScalarResult())
        ;
    }
}
