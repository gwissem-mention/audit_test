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
     * @param User $user
     * @return Token[]
     */
    public function getExpiresByUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('token');
        $queryBuilder
            ->where('token.user = :user')
            ->andWhere('token.expiresAt < :now')
            ->setParameters([
                'user' => $user,
                'now' => new \DateTime(),
            ])
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
