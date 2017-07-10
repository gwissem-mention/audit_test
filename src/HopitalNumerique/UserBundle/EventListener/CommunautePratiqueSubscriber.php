<?php

namespace HopitalNumerique\UserBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommunautePratiqueSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * CommunautePratiqueSubscriber constructor.
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::DISENROLL_USER => 'disenrollUser',
        ];
    }

    /**
     * Called when a user leave communaute de pratique
     *
     * @param EnrolmentEvent $event
     */
    public function disenrollUser(EnrolmentEvent $event)
    {
        $this->userManager->desinscritCommunautePratique($event->getUser());
    }
}
