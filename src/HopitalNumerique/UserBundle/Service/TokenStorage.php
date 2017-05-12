<?php

namespace HopitalNumerique\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Entity\Token;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Event\TokenEvent;
use HopitalNumerique\UserBundle\Repository\TokenRepository;
use HopitalNumerique\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TokenStorage
{
    /**
     * @var TokenRepository
     */
    protected $tokenRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * TokenStorage constructor.
     *
     * @param TokenRepository $tokenRepository
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TokenRepository $tokenRepository, EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->tokenRepository = $tokenRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create new token for session id and User
     *
     * @param $sessionId
     * @param User $user
     *
     * @return Token
     */
    public function createToken($sessionId, User $user)
    {
        $token = new Token($sessionId, $user);

        $this->entityManager->persist($token);
        $this->entityManager->flush($token);

        $this->eventDispatcher->dispatch(
            UserEvents::TOKEN_CREATED,
            new TokenEvent($token)
        );

        $this->removeExpiresByUser($user);

        return $token;
    }

    /**
     * Remove session token
     *
     * @param $sessionId
     */
    public function closeSession($sessionId)
    {
        $token = $this->tokenRepository->getBySession($sessionId);

        if (null !== $token) {
            $this->eventDispatcher->dispatch(
                UserEvents::TOKEN_DELETED,
                new TokenEvent($token)
            );

            $this->entityManager->remove($token);
            $this->entityManager->flush($token);
        }
    }

    /**
     * Remove all expired token for user
     *
     * @param User $user
     */
    public function removeExpiresByUser(User $user)
    {
        $tokens = $this->tokenRepository->getExpiresByUser($user);
        foreach ($tokens as $token) {
            $this->eventDispatcher->dispatch(
                UserEvents::TOKEN_DELETED,
                new TokenEvent($token)
            );

            $this->entityManager->remove($token);
        }

        $this->entityManager->flush();
    }
}
