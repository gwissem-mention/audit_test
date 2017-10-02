<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

/**
 * Class ReorderDiscussionHandler
 */
class ReorderDiscussionHandler
{
    /**
     * @var DiscussionRepository $discussionRepository
     */
    protected $discussionRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * ReorderDiscussionHandler constructor.
     *
     * @param DiscussionRepository $discussionRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(DiscussionRepository $discussionRepository, EntityManagerInterface $entityManager)
    {
        $this->discussionRepository = $discussionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReorderDiscussionCommand $command
     */
    public function handle(ReorderDiscussionCommand $command)
    {
        $discussions = $this->discussionRepository->findByIdsIndexed($this->getAllIds($command->order));

        $this->reorder($discussions, $command->order);

        $this->entityManager->flush();
    }

    protected function reorder(array $discussions, array $order, Discussion $parent = null)
    {
        foreach ($order as $id => $children) {

            $discussions[$id]->setParent($parent);

            if (count($children)) {
                $this->reorder($discussions, $children, $discussions[$id]);
            }
        }
    }

    /**
     * @param $order
     *
     * @return array
     */
    private function getAllIds($order)
    {
        $ids = [];

        foreach ($order as $id => $children) {
            $ids[] = $id;

            if (count($children)) {
                $ids = array_merge($ids, $this->getAllIds($children));
            }
        }

        return $ids;
    }
}
