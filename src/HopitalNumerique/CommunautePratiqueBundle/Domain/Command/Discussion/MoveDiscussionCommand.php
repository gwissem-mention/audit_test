<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Class MoveDiscussionCommand
 */
class MoveDiscussionCommand
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
     * MoveDiscussionCommand constructor.
     *
     * @param Discussion $discussion
     * @param Groupe $group
     */
    public function __construct(Discussion $discussion, Groupe $group)
    {
        $this->discussion = $discussion;
        $this->group = $group;
    }
}
