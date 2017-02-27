<?php

namespace HopitalNumerique\UserBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent as UserEventFos;

class LoginManager implements EventSubscriberInterface
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    private $securityContext;

    /**
     * Constructor.
     *
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onSecurityImplicitLogin',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        ];
    }

    /**
     * [onSecurityImplicitLogin description].
     *
     * @param FOS\UserBundle\Event\UserEvent $event [description]
     *
     * @return [type]
     */
    public function onSecurityImplicitLogin(UserEventFos $event)
    {
        $user = $event->getUser();

        //if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
            $user->addNbVisites();
        //}
    }

    /**
     * [onSecurityInteractivelogin description].
     *
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event [description]
     *
     * @return [type]
     */
    public function onSecurityInteractivelogin(\Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        //if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
            $user->addNbVisites();
        //}

        $ip = $event->getRequest()->getClientIp();
        $user->setIpLastConnection($ip);
    }
}
