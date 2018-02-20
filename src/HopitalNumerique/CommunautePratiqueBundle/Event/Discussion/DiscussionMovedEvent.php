<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DiscussionMovedEvent
 */
class DiscussionMovedEvent extends Event
{
    /**
     * @var Discussion $discussion
     */
    public $discussion;

    /**
     * @var Groupe $group
     */
    public $group;

    /**
     * DiscussionMovedEvent constructor.
     *
     * @param Discussion $discussion
     * @param Groupe $group
     */
    public function __construct(Discussion $discussion, Groupe $group)
    {
        $this->discussion = $discussion;
        $this->group = $group;
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
