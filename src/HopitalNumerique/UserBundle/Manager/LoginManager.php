<?php

namespace HopitalNumerique\UserBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use FOS\UserBundle\FOSUserEvents;
use HopitalNumerique\UserBundle\Manager\UserEvent;

class LoginManager implements EventSubscriberInterface
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    private $securityContext;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /**
     * Constructor
     *
     * @param SecurityContext $securityContext
     * @param Doctrine        $doctrine
     */
    public function __construct(SecurityContext $securityContext, Doctrine $doctrine)
    {
        $this->securityContext = $securityContext;
        $this->em = $doctrine->getManager();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
                FOSUserEvents::SECURITY_IMPLICIT_LOGIN  => 'onSecurityImplicitLogin',
                FOSUserEvents::REGISTRATION_COMPLETED   => 'onRegistrationCompleted'
        );
    }

    /**
     * [onSecurityImplicitLogin description]
     *
     * @param  UserEvent $event [description]
     *
     * @return [type]
     */
    public function onSecurityImplicitLogin(UserEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
            $user->addNbVisites();
        }
    }

    /**
     * [onSecurityInteractivelogin description]
     *
     * @param  \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event [description]
     *
     * @return [type]
     */
    public function onSecurityInteractivelogin(\Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
            $user->addNbVisites();
        }
    }
}