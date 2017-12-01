<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use Symfony\Component\EventDispatcher\Event;

class DiscussionVisibilityEvent extends Event
{
    /**
     * @var Discussion
     */
    protected $discussion;

    /**
     * ActivityRegistrationEvent constructor.
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
