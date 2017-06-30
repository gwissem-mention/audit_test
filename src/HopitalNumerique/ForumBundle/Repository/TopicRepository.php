<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Forum;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * TopicRepository.
 */
class TopicRepository extends EntityRepository
{
    /**
     * @param integer $topicId
     *
     * @return Topic
     */
    public function findByIdWithJoin($topicId)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.board', 'b')->addSelect('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->leftJoin('c.domaines', 'd')->addSelect('d')
            ->leftJoin('c.forum', 'f')->addSelect('f')

            ->andWhere('t.id = :topicId')->setParameter('topicId', $topicId)

            ->setMaxResults(1)
            ->getQuery()->getSingleResult()
        ;
    }

    /**
     * @param array $topicIds
     *
     * @return Topic[]
     */
    public function findByIdsWithJoin($topicIds)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.board', 'b')->addSelect('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->leftJoin('c.domaines', 'd')->addSelect('d')
            ->leftJoin('c.forum', 'f')->addSelect('f')

            ->andWhere('t.id IN (:topicIds)')->setParameter('topicIds', $topicIds)

            ->getQuery()->getResult()
        ;
    }

    /**
     * Récupère les derniers topics commentés par type de forum.
     *
     * @param      $id
     * @param null $limit
     *
     * @return QueryBuilder
     */
    public function getLastTopicsForum($id, $limit = null, $role = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('topic')
            ->from('\HopitalNumerique\ForumBundle\Entity\Topic', 'topic')
            ->innerJoin('topic.lastPost', 'post')
            ->innerJoin('topic.board', 'board')
            ->innerJoin('board.category', 'cat')
            ->innerJoin('cat.forum', 'forum', Join::WITH, 'forum.id = :idForum')
            ->setParameter('idForum', $id)
        ;

        if (!is_null($role)) {
            $qb
                ->andWhere('board.readAuthorisedRoles LIKE :role')
                ->setParameter('role', '%' . $role . '%')
            ;
        }

        $qb
            ->groupBy('topic.id')
            ->orderBy('post.createdDate', 'DESC')
        ;

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Récupère les derniers topics commentés par type de forum.
     *
     * @param $id
     * @param null $limit
     * @param $epingle
     * @param $idCat
     *
     * @return QueryBuilder
     */
    public function getLastTopicsForumEpingle($id, $limit = null, $epingle, $idCat)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('topic')
            ->from('\HopitalNumerique\ForumBundle\Entity\Topic', 'topic')
            ->innerJoin('topic.lastPost', 'post')
            ->innerJoin('topic.board', 'board')
            ->innerJoin('board.category', 'cat')
            ->innerJoin('cat.forum', 'forum', Join::WITH, 'forum.id = :idForum')
            ->andWhere('cat.id = :idCategorie')
            ->andWhere('topic.isSticky = :sticky')
            ->setParameter('idForum', $id)
            ->setParameter('sticky', $epingle)
            ->setParameter('idCategorie', $idCat)
            ->groupBy('topic.id')
            ->orderBy('post.createdDate', 'DESC')
        ;

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Retourne les topics d'un forum.
     *
     * @param Forum $forum Forum
     *
     * @return Topic[] Topics
     */
    public function findByForum(Forum $forum)
    {
        $query = $this->createQueryBuilder('topic');

        $query
            ->addSelect(['board', 'category'])
            ->innerJoin('topic.board', 'board')
            ->innerJoin('board.category', 'category', Join::WITH, 'category.forum = :forum')
            ->setParameter('forum', $forum)
            ->orderBy('category.listOrderPriority')
            ->addOrderBy('board.listOrderPriority')
            ->addOrderBy('topic.lastPost', 'DESC')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne le nombre de fils d'un forum.
     *
     * @param int $forumId ID du forum
     *
     * @return int Nombre
     */
    public function getCountForForum($forumId)
    {
        $qb = $this->createQueryBuilder('topic');

        $qb
            ->select($qb->expr()->count('topic'))
            ->innerJoin('topic.board', 'board')
            ->innerJoin('board.category', 'category', Join::WITH, $qb->expr()->eq('category.forum', ':forumId'))
            ->setParameter('forumId', $forumId)
        ;

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getLastTopicWhereUserIsInvolved(User $user)
    {
        return $this->createQueryBuilder('t')
            ->join('t.lastPost', 'flp')
            ->leftJoin('t.board', 'b')->addSelect('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->leftJoin('c.forum', 'f')->addSelect('f')
            ->leftJoin('t.posts', 'fp', Join::WITH, 'fp.createdBy = :userId')
            ->leftJoin('t.subscriptions', 's', Join::WITH, 's.ownedBy = :userId AND s.isSubscribed = 1')
            ->setParameter('userId', $user->getId())

            ->andWhere('s.id IS NOT NULL OR fp.id IS NOT NULL')

            ->addOrderBy('flp.createdDate', 'DESC')

            ->setMaxResults(5)
            ->getQuery()->getResult()
        ;
    }

    /**
     * Count Topic with a least one post, by domains. If $since is givent, filter posts created after $since.
     *
     * @param Domaine[] $domains
     * @param \DateTime|null $since
     *
     * @return mixed
     */
    public function countActiveTopicsByDomains($domains, \DateTime $since = null)
    {
        $qb = $this->createQueryBuilder('topic');
        $qb
            ->select('COUNT(DISTINCT(topic.id))')
            ->join('topic.posts', 'posts')
            ->join('topic.board', 'board')
            ->join('board.category', 'category')
            ->join('category.forum', 'forum')
            ->join(
                'forum.domain',
                'domain',
                Join::WITH,
                $qb->expr()->in(
                    'domain',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
        ;

        if (null !== $since) {
            $qb
                ->andWhere('posts.createdDate >= :since')
                ->setParameters([
                    'since' => $since
                ])
            ;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count unreplyed topics by domains
     *
     * @param Domaine[] $domains
     * @return mixed
     */
    public function countUnreplyedTopcisByDomains($domains)
    {
        $qb = $this->createQueryBuilder('topic');
        $qb
            ->select('COUNT(DISTINCT(topic.id))')
            ->join('topic.board', 'board')
            ->join('board.category', 'category')
            ->join('category.forum', 'forum')
            ->join(
                'forum.domain',
                'domain',
                Join::WITH,
                $qb->expr()->in(
                    'domain',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
            ->andWhere($qb->expr()->eq('topic.cachedReplyCount', 0))
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
