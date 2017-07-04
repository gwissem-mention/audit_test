<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk;

use Doctrine\ORM\EntityManager;

/**
 * Class ReorderRelatedRisksHandler
 */
class ReorderRelatedRisksHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * ReorderRelatedBoardsHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReorderRelatedRisksCommand $command
     */
    public function handle(ReorderRelatedRisksCommand $command)
    {
        $reorderedRisks = [];
        foreach ($command->risks as $k => $reorderedRisk) {
            $reorderedRisks[$reorderedRisk['id']] = $k + 1;
        }

        foreach ($command->object->getRelatedRisks() as $risk) {
            $risk->setPosition($reorderedRisks[$risk->getRisk()->getId()]);
        }

        $this->entityManager->flush();
    }
}
