<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

/**
 * Class DiscussionViewedEvent
 */
class DiscussionViewedEvent extends Event
{
    /**
     * @var Discussion
     */
    protected $discussion;

    /**
     * @var User
     */
    protected $user;

    /**
     * DiscussionCreatedEvent constructor.
     *
     * @param Discussion $discussion
     * @param User|null $user
     */
    public function __construct(Discussion $discussion, User $user = null)
    {
        $this->discussion = $discussion;
        $this->user = $user;
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
