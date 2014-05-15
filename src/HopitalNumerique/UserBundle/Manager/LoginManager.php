<?php

namespace HopitalNumerique\UserBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine; 
use EasyApp\UserBundle\Entity\UserLogin;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserBundle;


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

    public function onSecurityImplicitLogin(UserEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();

        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
            $user->addNbVisites();
        }
    }

    public function onSecurityInteractivelogin(\Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();

        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
            $user->addNbVisites();
        }
    }
}