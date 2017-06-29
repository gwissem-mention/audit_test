<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\RoleBundle\Entity\Role;

/**
 * TopicRepository.
 */
class PostRepository extends EntityRepository
{
    /**
     * Retrieves the first post of each board topic.
     *
     * @param int $boardId
     *
     * @return array
     */
    public function getFirstPostsFromBoard($boardId)
    {
        $qb = $this->createQueryBuilder('post');

        $qb
            ->leftJoin('post.topic', 'topic')
            ->leftJoin('topic.board', 'board')
            ->where('board.id = :boardId')
            ->setParameter('boardId', $boardId)
            ->groupBy('topic.id')
            ->orderBy('post.createdDate', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     *
     * @return integer
     */
    public function countPostForUser(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(p)')
            ->from(Post::class, 'p')
            ->andWhere('p.createdBy = :userId')->setParameter('userId', $user->getId())
            ->andWhere('p.isDeleted = false')

            ->getQuery()->getSingleScalarResult()
        ;
    }

    /**
     * Count posts by domains. If $since is given, filter on posts created after $since
     *
     * @param Domaine[] $domains
     * @param \DateTime|null $since
     *
     * @return mixed
     */
    public function countPostsByDomains($domains, \DateTime $since = null)
    {
        $qb = $this->createQueryBuilder('post');
        $qb
            ->select('COUNT(post.id)')
            ->join('post.topic', 'topic')
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
                ->andWhere('post.createdDate >= :since')
                ->setParameter('since', $since)
            ;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count export contribution by domains. If $since is given, filter on posts created after $since
     *
     * @param $domains
     * @param \DateTime|null $since
     */
    public function countExpertContributionByDomains($domains, \DateTime $since = null)
    {
        $qb = $this->createQueryBuilder('post');
        $qb
            ->select('COUNT(post.id)')
            ->join(
                'post.createdBy',
                'user',
                Join::WITH,
                $qb->expr()->like(
                    'user.roles',
                    $qb->expr()->literal(sprintf('%%%s%%', Role::$ROLE_EXPERT_LABEL))
                )
            )
        ;

        if (null !== $since) {
            $qb
                ->andWhere('post.createdDate >= :since')
                ->setParameter('since', $since)
            ;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
