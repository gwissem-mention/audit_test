<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\RelatedBoard;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\ForumBundle\Repository\BoardRepository;

/**
 * Class LinkBoardToObjectHandler
 */
class LinkBoardToObjectHandler
{
    /**
     * @var ObjetRepository
     */
    protected $objectRepository;

    /**
     * @var ObjetManager
     */
    protected $objectManager;

    /**
     * @var BoardRepository
     */
    protected $boardRepository;

    /**
     * LinkBoardToObjectHandler constructor.
     *
     * @param ObjetRepository $objectRepository
     * @param ObjetManager    $objectManager
     * @param BoardRepository $boardRepository
     */
    public function __construct(
        ObjetRepository $objectRepository,
        ObjetManager $objectManager,
        BoardRepository $boardRepository
    ) {
        $this->objectRepository = $objectRepository;
        $this->objectManager = $objectManager;
        $this->boardRepository = $boardRepository;
    }

    /**
     * @param LinkBoardToObjectCommand $command
     */
    public function handle(LinkBoardToObjectCommand $command)
    {
        /** @var Objet $object */
        $currentObject = $this->objectRepository->findOneBy(
            ['id' => $command->objectId]
        );

        $relatedBoards = $currentObject->getRelatedBoards();
        $boardsId = [];

        /** @var RelatedBoard $relatedBoard */
        foreach ($relatedBoards as $relatedBoard) {
            $boardsId[] = $relatedBoard->getBoard()->getId();
        }

        $boards = $this->boardRepository->findBy(['id' => $command->boardsId]);

        foreach ($boards as $board) {
            $currentObject->linkBoard($board);
        }

        $this->objectManager->save($currentObject);
    }
}