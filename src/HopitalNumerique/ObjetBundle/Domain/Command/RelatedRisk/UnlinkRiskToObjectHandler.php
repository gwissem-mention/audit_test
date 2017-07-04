<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;

/**
 * Class UnlinkRiskToObjectHandler
 */
class UnlinkRiskToObjectHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * UnlinkRiskToObjectHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UnlinkRiskToObjectCommand $command
     */
    public function handle(UnlinkRiskToObjectCommand $command)
    {
        $relatedRisk = $command->object->getRelatedRisks()->filter(function (RelatedRisk $relatedRisk) use ($command) {
            return $relatedRisk->getRisk() === $command->risk;
        })->first();

        $this->entityManager->remove($relatedRisk);

        $this->entityManager->flush();
    }
}
