<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\Token;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * TokenRepository.
 */
class TokenRepository extends EntityRepository
{
    /**
     * Find token by session id
     *
     * @param $sessionId
     * @return null|Token|object
     */
    public function getBySession($sessionId)
    {
        return $this->findOneBy([
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * Get all expired tokens for a user
     *
     * @param User|null $user
     * @return Token[]
     */
    public function getExpires(User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('token');
        $queryBuilder
            ->andWhere('token.expiresAt < :now')
            ->setParameter('now', new \DateTime())
        ;

        if (null !== $user) {
            $queryBuilder
                ->andWhere('token.user = :user')
                ->setParameter('user', $user)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds current active token for session id and user.
     *
     * @param $sessionId
     * @param User|null $user
     *
     * @return Token|null
     */
    public function findActive($sessionId, User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('token');
        $queryBuilder
            ->andWhere('token.expiresAt >= :now')
            ->andWhere('token.sessionId = :session')
            ->setMaxResults(1)
            ->setParameters([
                'now' => new \DateTime(),
                'session' => $sessionId,
            ])
        ;

        if (null !== $user) {
            $queryBuilder
                ->andWhere('token.user = :user')
                ->setParameter('user', $user)
            ;
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
