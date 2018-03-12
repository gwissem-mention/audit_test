<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;

/**
 * Class MoveDiscussionCommand
 */
class MoveMessageCommand
{
    /**
     * @var Message $message
     */
    public $message;

    /**
     * @var Discussion $discussion
     */
    public $discussion;

    /**
     * MoveMessageCommand constructor.
     *
     * @param Message $message
     * @param Discussion $discussion
     */
    public function __construct(Message $message, Discussion $discussion)
    {
        $this->message = $message;
        $this->discussion = $discussion;
    }
}
