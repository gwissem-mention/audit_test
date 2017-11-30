<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\CDPDiscussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

class CDPDiscussionFactory extends Factory
{
    /**
     * @var DiscussionRepository $discussionRepository
     */
    protected $discussionRepository;

    /**
     * CDPDiscussionFactory constructor.
     *
     * @param DiscussionRepository $containerRepository
     */
    public function __construct(DiscussionRepository $containerRepository)
    {
        $this->discussionRepository = $containerRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::CDP_DISCUSSION_TYPE;
    }

    /**
     * @param Discussion $discussion
     *
     * @return CDPDiscussion
     */
    public function build($discussion)
    {
        return new CDPDiscussion($discussion);
    }

    /**
     * @param $itemIds
     *
     * @return Discussion[]
     */
    public function getMultiple($itemIds)
    {
        return $this->discussionRepository->findById($itemIds);
    }

    /**
     * @param $itemId
     *
     * @return null|Discussion
     */
    public function get($itemId)
    {
        return $this->discussionRepository->find($itemId);
    }
}
