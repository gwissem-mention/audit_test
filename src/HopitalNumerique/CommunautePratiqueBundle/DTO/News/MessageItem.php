<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\UserBundle\Entity\User;

class MessageItem implements WallItemInterface
{
    /**
     * @var Message $message
     */
    protected $message;

    /**
     * MessageItem constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->message->getDiscussion();
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->message->getUser();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->message->getContent();
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->message->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'message';
    }

}
