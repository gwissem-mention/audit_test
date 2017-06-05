<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\RelatedBoard;
use HopitalNumerique\ObjetBundle\Repository\RelatedBoardRepository;

/**
 * Class ReorderRelatedBoardsHandler
 */
class ReorderRelatedBoardsHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var RelatedBoardRepository
     */
    protected $relatedBoardRepository;

    /**
     * ReorderRelatedBoardsHandler constructor.
     *
     * @param EntityManager          $entityManager
     * @param RelatedBoardRepository $relatedBoardRepository
     */
    public function __construct(EntityManager $entityManager, RelatedBoardRepository $relatedBoardRepository)
    {
        $this->entityManager = $entityManager;
        $this->relatedBoardRepository = $relatedBoardRepository;
    }

    /**
     * @param ReorderRelatedBoardsCommand $command
     */
    public function handle(ReorderRelatedBoardsCommand $command)
    {
        $relatedBoards = $this
            ->relatedBoardRepository
            ->findBy(['object' => $command->object->getId()])
        ;

        $relatedBoardsIndexedByBoardId = [];

        /** @var RelatedBoard $relatedBoard */
        foreach ($relatedBoards as $relatedBoard) {
            $relatedBoardsIndexedByBoardId[$relatedBoard->getBoard()->getId()] = $relatedBoard;
        }

        $i = 1;

        foreach ($command->boards as $board) {
            if ($relatedBoardsIndexedByBoardId[$board['id']]->getPosition() != $i) {
                $relatedBoardsIndexedByBoardId[$board['id']]->setPosition($i);
            }

            $i++;
        }

        $this->entityManager->flush($relatedBoardsIndexedByBoardId);
    }
}
