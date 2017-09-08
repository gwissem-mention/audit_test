<?php

namespace Nodevo\MailBundle\Event;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class RecommendationLoggerEvent extends Event
{
    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var string $recipientEmail
     */
    protected $recipientEmail;

    /**
     * RecommendationSendedEvent constructor.
     *
     * @param string $recipientEmail
     * @param User|null $user
     */
    public function __construct($recipientEmail, User $user = null)
    {
        $this->recipientEmail = $recipientEmail;
        $this->user = $user;
    }

    /**
     * @return null|User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }
}
