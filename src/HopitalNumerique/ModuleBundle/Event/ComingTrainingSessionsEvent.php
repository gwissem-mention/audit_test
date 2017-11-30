<?php

namespace HopitalNumerique\ModuleBundle\Event;

use HopitalNumerique\ModuleBundle\Entity\Session;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ComingTrainingSessionsEvent.
 */
class ComingTrainingSessionsEvent extends Event
{
    /**
     * @var Session $session
     */
    protected $session;

    /**
     * NextSessionsNotificationRequired constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
