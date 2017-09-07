<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;

/**
 * Class LinkRisksToObjectHandler
 */
class LinkRisksToObjectHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var RiskRepository $riskRepository
     */
    protected $riskRepository;

    /**
     * LinkRisksToObjectHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RiskRepository $riskRepository
     */
    public function __construct(EntityManagerInterface $entityManager, RiskRepository $riskRepository)
    {
        $this->entityManager = $entityManager;
        $this->riskRepository = $riskRepository;
    }

    /**
     * @param LinkRisksToObjectCommand $command
     */
    public function handle(LinkRisksToObjectCommand $command)
    {
        $boards = $this->riskRepository->findBy(['id' => $command->risksId]);

        foreach ($boards as $board) {
            $command->object->linkRisk($board);
        }

        $this->entityManager->flush();
    }
}
