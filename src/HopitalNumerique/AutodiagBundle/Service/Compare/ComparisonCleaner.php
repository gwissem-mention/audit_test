<?php

namespace HopitalNumerique\AutodiagBundle\Service\Compare;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\CompareRepository;

class ComparisonCleaner
{
    /** @var CompareRepository */
    protected $compareRepository;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * ComparisonCleaner constructor.
     * @param CompareRepository $compareRepository
     */
    public function __construct(CompareRepository $compareRepository, EntityManager $entityManager)
    {
        $this->compareRepository = $compareRepository;
        $this->entityManager = $entityManager;
    }

    public function cleanRelatedToSynthesis(Synthesis $synthesis)
    {
        $related = $this->compareRepository->findRelatedToSynthesis($synthesis);
        foreach ($related as $compare) {
            $this->entityManager->remove($compare->getSynthesis());
            $this->entityManager->remove($compare->getReference());
            $this->entityManager->remove($compare);
        }

        $this->entityManager->flush();
    }
}
