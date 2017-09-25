<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;

class DeleteMessageCommand
{
    /**
     * @var Message $message
     */
    public $message;

    /**
     * DeleteMessageCommand constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
