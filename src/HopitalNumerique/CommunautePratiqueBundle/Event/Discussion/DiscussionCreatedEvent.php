<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

class DiscussionCreatedEvent extends Event
{
    /**
     * @var Discussion
     */
    protected $discussion;

    /**
     * DiscussionCreatedEvent constructor.
     *
     * @param Discussion $discussion
     */
    public function __construct(Discussion $discussion)
    {
        $this->discussion = $discussion;
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }
}
