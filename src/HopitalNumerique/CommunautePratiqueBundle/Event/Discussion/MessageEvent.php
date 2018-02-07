<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use Symfony\Component\EventDispatcher\Event;

abstract class MessageEvent extends Event
{
    /**
     * @var Message $message
     */
    protected $message;

    /**
     * MessagePosted constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
