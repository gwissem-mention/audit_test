<?php

namespace HopitalNumerique\UserBundle\EventListener;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Service\TokenStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\SecurityEvents;

class TokenSubscriber implements EventSubscriberInterface, LogoutHandlerInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * TokenSubscriber constructor.
     *
     * @param SessionInterface $session
     * @param TokenStorage $tokenStorage
     */
    public function __construct(SessionInterface $session, TokenStorage $tokenStorage)
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'createToken',
        ];
    }

    /**
     * Create token on login
     *
     * @param InteractiveLoginEvent $event
     */
    public function createToken(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $this->tokenStorage->createToken(
                $this->session->getId(),
                $user
            );
        }
    }

    /**
     * Remove token on logout
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->tokenStorage->closeSession(
            $request->cookies->get(ini_get('session.name'))
        );
    }
}
