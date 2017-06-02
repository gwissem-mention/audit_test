<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\RelatedBoard;

/**
 * Class UnlinkBoardToObjectHandler
 */
class UnlinkBoardToObjectHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * UnlinkBoardToObjectHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UnlinkBoardToObjectCommand $command
     */
    public function handle(UnlinkBoardToObjectCommand $command)
    {
        /** @var RelatedBoard $relatedBoard */
        $relatedBoard = $this
            ->entityManager
            ->getRepository(RelatedBoard::class)
            ->findOneBy(['object' => $command->object->getId(), 'board' => $command->board->getId()])
        ;

        $command->object->removeRelatedBoard($relatedBoard);

        $this->entityManager->flush($command->object);
    }
}
