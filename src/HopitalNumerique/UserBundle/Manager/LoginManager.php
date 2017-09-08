<?php

namespace HopitalNumerique\UserBundle\Manager;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent as UserEventFos;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginManager implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onSecurityImplicitLogin',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        ];
    }

    /**
     * @param UserEventFos $event
     */
    public function onSecurityImplicitLogin(UserEventFos $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        $user->addVisitCount();
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractivelogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $user->addVisitCount();

        $ip = $event->getRequest()->getClientIp();
        $user->setIpLastConnection($ip);
    }
}
