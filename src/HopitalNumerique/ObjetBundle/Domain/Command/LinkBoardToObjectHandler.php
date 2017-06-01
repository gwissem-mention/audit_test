<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\RelatedBoard;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use CCDNForum\ForumBundle\Model\Component\Repository\BoardRepository;

/**
 * Class LinkBoardToObjectHandler
 */
class LinkBoardToObjectHandler
{
    private $objectRepository;

    private $objectManager;

    private $boardRepository;

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

        foreach ($command->boardsId as $selectedBoardId) {
            if (!in_array($selectedBoardId, $boardsId)) {
                $board = $this->boardRepository->findOneBoardById($selectedBoardId);
                $currentObject->addRelatedBoard(new RelatedBoard($currentObject, $board));
            }
        }

        $this->objectManager->save($currentObject);
    }
}
