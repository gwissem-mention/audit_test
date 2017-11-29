<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Activity;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

class PublicItem implements WallItemInterface
{
    /**
     * @var Discussion $discussion
     */
    protected $discussion;

    /**
     * @var Activity
     */
    protected $activity;

    /**
     * DiscussionItem constructor.
     *
     * @param Activity $activity
     * @param Discussion $discussion
     */
    public function __construct(Activity $activity, Discussion $discussion)
    {
        $this->discussion = $discussion;
        $this->activity = $activity;
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
     * @return Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->activity->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'public';
    }
}
