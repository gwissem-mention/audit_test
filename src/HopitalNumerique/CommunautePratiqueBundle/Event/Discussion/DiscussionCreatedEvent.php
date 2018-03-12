<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

class DiscussionCreatedEvent extends Event
{
    /**
     * @var Discussion
     */
    protected $discussion;

    /**
     * @var Groupe
     */
    protected $group;

    /**
     * DiscussionCreatedEvent constructor.
     *
     * @param Discussion $discussion
     * @param Groupe $group
     */
    public function __construct(Discussion $discussion, Groupe $group = null)
    {
        $this->discussion = $discussion;
        if (null !== $group) {
            $this->group = $group;
        }
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * @return Groupe
     */
    public function getGroup()
    {
        return $this->group;
    }
}
