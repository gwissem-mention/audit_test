<?php

namespace HopitalNumerique\ReferenceBundle\Domain\Command;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use HopitalNumerique\ReferenceBundle\Service\ReferenceSwitcher;

class SwitchReferenceHandler
{
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var ReferenceSwitcher
     */
    private $referenceSwitcher;

    /**
     * SwitchReferenceHandler constructor.
     *
     * @param ReferenceRepository $referenceRepository
     * @param ReferenceSwitcher   $referenceSwitcher
     */
    public function __construct(ReferenceRepository $referenceRepository, ReferenceSwitcher $referenceSwitcher)
    {
        $this->referenceRepository = $referenceRepository;
        $this->referenceSwitcher = $referenceSwitcher;
    }

    /**
     * @param SwitchReferenceCommand $switchReferenceCommand
     *
     * @throws \LogicException
     */
    public function handle(SwitchReferenceCommand $switchReferenceCommand)
    {
        /** @var Reference $currentReference */
        $currentReference = $this->referenceRepository->findOneBy(['id' => $switchReferenceCommand->currentReference]);

        if (is_null($currentReference)) {
            throw new \LogicException('La référence actuelle n\'existe pas.');
        }

        /** @var Reference $targetReference */
        $targetReference = $this->referenceRepository->findOneBy(['id' => $switchReferenceCommand->targetReference]);

        if (is_null($targetReference)) {
            throw new \LogicException('La référence cible n\'existe pas.');
        }

        $this->referenceSwitcher->switchReferences($currentReference, $targetReference);

        if ($switchReferenceCommand->keepHistory) {
            $this->referenceSwitcher->importSearchHistory($currentReference, $targetReference);
        }
    }
}
