<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

class DiscussionItem implements WallItemInterface
{
    /**
     * @var Discussion $discussion
     */
    protected $discussion;

    /**
     * DiscussionItem constructor.
     *
     * @param Discussion $discussion
     */
    public function __construct(Discussion $discussion)
    {
        $this->discussion = $discussion;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->discussion->getTitle();
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->discussion->getUser();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->discussion->getMessages()->first()->getContent();
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->discussion->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'discussion';
    }
}
