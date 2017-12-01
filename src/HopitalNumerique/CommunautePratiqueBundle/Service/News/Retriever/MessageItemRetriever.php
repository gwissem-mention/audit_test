<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\MessageItem;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;

class MessageItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * MessageItemRetriever constructor
     *
     * @param MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        $items = [];
        foreach ($this->messageRepository->getRecentPublicMessage($domain) as $message) {
            $items[] = new MessageItem($message);
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
