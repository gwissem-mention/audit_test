<?php

namespace HopitalNumerique\UserBundle\EventListener;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Service\TokenStorage as AuthTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class TokenSubscriber implements EventSubscriberInterface, LogoutHandlerInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var TokenStorage
     */
    protected $authTokenStorage;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * TokenSubscriber constructor.
     *
     * @param SessionInterface $session
     * @param AuthTokenStorage|TokenStorage $authTokenStorage
     * @param TokenStorage $tokenStorage
     */
    public function __construct(SessionInterface $session, AuthTokenStorage $authTokenStorage, TokenStorage $tokenStorage)
    {
        $this->session = $session;
        $this->authTokenStorage = $authTokenStorage;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'createToken',
        ];
    }

    /**
     * Create token on login
     *
     * @param GetResponseEvent $event
     */
    public function createToken(GetResponseEvent $event)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        $this->authTokenStorage->createToken(
            $this->session->getId(),
            $user instanceof User ? $user : null
        );
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
        $this->authTokenStorage->closeSession(
            $request->cookies->get(ini_get('session.name'))
        );
    }
}
