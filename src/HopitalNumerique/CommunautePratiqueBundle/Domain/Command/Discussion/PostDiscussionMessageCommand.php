<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

class PostDiscussionMessageCommand
{
    /**
     * @var Discussion $discussion
     */
    public $discussion;

    /**
     * @var User $author
     */
    public $author;

    /**
     * @var string $content
     */
    public $content;

    /**
     * @var Message $message
     */
    public $message;

    /**
     * PostDiscussionMessageCommand constructor.
     *
     * @param Discussion $discussion
     * @param User $author
     * @param Message|null $message
     */
    public function __construct(Discussion $discussion, User $author, Message $message = null)
    {
        $this->discussion = $discussion;
        $this->author = $author;
        $this->message = $message;

        if (null !== $message) {
            $this->content = $message->getContent();
        }
    }
}
