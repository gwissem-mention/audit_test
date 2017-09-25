<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
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
     * PostDiscussionMessageCommand constructor.
     *
     * @param Discussion $discussion
     * @param User $author
     * @param $content
     */
    public function __construct(Discussion $discussion, User $author, $content)
    {
        $this->discussion = $discussion;
        $this->author = $author;
        $this->content = $content;
    }
}
