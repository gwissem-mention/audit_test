<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\DiscussionItem;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

class DiscussionItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var DiscussionRepository $discussionRepository
     */
    protected $discussionRepository;

    /**
     * DiscussionItemRetriever constructor.
     *
     * @param DiscussionRepository $discussionRepository
     */
    public function __construct(DiscussionRepository $discussionRepository)
    {
        $this->discussionRepository = $discussionRepository;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        $items = [];
        foreach ($this->discussionRepository->getRecentPublicDiscussion($domain) as $discussion) {
            $items[] = new DiscussionItem($discussion);
        }

        return $items;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return true;
    }
}
