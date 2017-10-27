<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\CartBundle\Entity\Item as ItemEntity;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

class CDPDiscussion extends Item
{
    /**
     * @var Discussion $discussion
     */
    protected $discussion;

    /**
     * Publication constructor.
     *
     * @param Discussion $discussion
     */
    public function __construct(Discussion $discussion)
    {
        $this->discussion = $discussion;
    }

    /**
     * @return Discussion
     */
    public function getObject()
    {
        return $this->discussion;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->discussion->getTitle();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return ItemEntity::CDP_DISCUSSION_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->discussion->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'hopitalnumerique_communautepratique_discussions_public_desfult_discussion';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'discussion' => $this->discussion->getId(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->discussion->getDomains();
    }
}
