<?php

namespace HopitalNumerique\StatBundle\EventListener;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\StatBundle\Entity\StatConnections;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginStatSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CurrentDomaine
     */
    private $currentDomaine;

    /**
     * SecuritySubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager $entityManager
     * @param CurrentDomaine $currentDomaine
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManager $entityManager,
        CurrentDomaine $currentDomaine
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->currentDomaine = $currentDomaine;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    /**
     * Add connection statistic
     */
    public function onSecurityInteractiveLogin()
    {
        $statConnection = new StatConnections(
            $this->currentDomaine->get(),
            $this->tokenStorage->getToken()->getUser()
        );

        $this->entityManager->persist($statConnection);
        $this->entityManager->flush();
    }
}
