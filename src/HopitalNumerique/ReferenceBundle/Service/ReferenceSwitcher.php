<?php

namespace HopitalNumerique\ReferenceBundle\Service;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;
use HopitalNumerique\StatBundle\Entity\StatRecherche;
use HopitalNumerique\StatBundle\Repository\StatRechercheRepository;

class ReferenceSwitcher
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityHasReferenceRepository
     */
    private $entityHasReferenceRepository;

    /**
     * @var StatRechercheRepository
     */
    private $searchStatRepository;

    /**
     * ReferenceSwitcher constructor.
     *
     * @param EntityManager                $entityManager
     * @param EntityHasReferenceRepository $entityHasReferenceRepository
     * @param StatRechercheRepository      $statRechercheRepository
     */
    public function __construct(
        EntityManager $entityManager,
        EntityHasReferenceRepository $entityHasReferenceRepository,
        StatRechercheRepository $statRechercheRepository
    ) {
        $this->entityManager = $entityManager;
        $this->entityHasReferenceRepository = $entityHasReferenceRepository;
        $this->searchStatRepository = $statRechercheRepository;
    }

    /**
     * @param Reference $currentReference
     * @param Reference $targetReference
     *
     * @throws \Exception
     */
    public function switchReferences(Reference $currentReference, Reference $targetReference)
    {
        $currentEntityHasReferences = $this->entityHasReferenceRepository->findByReference($currentReference);
        $targetEntityHasReferences = $this->entityHasReferenceRepository->findByReference($targetReference);

        $entitiesHasRefToBeAdded = array_udiff(
            $currentEntityHasReferences,
            $targetEntityHasReferences,
            function ($a, $b) {
                if ($a['entityId'] . '-' . $a['entityType'] === $b['entityId'] . '-' . $b['entityType']) {
                    return 0;
                }

                return $a['entityId'] . '-' . $a['entityType'] > $b['entityId'] . '-' . $b['entityType'] ? 1 : -1;
            }
        );

        $this->entityManager->beginTransaction();

        try {
            /** @var EntityHasReference $currentEntityHasReference */
            foreach ($entitiesHasRefToBeAdded as $currentEntityHasReference) {
                $newEntityHasReference = new EntityHasReference();
                $newEntityHasReference->setReference($targetReference);
                $newEntityHasReference->setEntityId($currentEntityHasReference['entityId']);
                $newEntityHasReference->setEntityType($currentEntityHasReference['entityType']);
                $newEntityHasReference->setPrimary($currentEntityHasReference['primary']);

                $this->entityManager->persist($newEntityHasReference);
                $this->entityManager->flush($newEntityHasReference);
                $this->entityManager->detach($newEntityHasReference);
            }

            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw new \Exception('Une erreur s\'est produite lors de l\'ajout des références');
        }
    }

    /**
     * @param Reference $currentReference
     * @param Reference $targetReference
     */
    public function importSearchHistory(Reference $currentReference, Reference $targetReference)
    {
        $currentRefSearchHistory = $this->searchStatRepository->findSearchHistoryByReferences(
            $currentReference,
            $targetReference
        );

        /** @var StatRecherche $searchStat */
        foreach ($currentRefSearchHistory as $searchStat) {
            $refs = json_decode($searchStat->getRequete());

            $refs[] = (string) $targetReference->getId();

            $refs = json_encode($refs);

            $searchStat->setRequete($refs);
        }

        $this->entityManager->flush();
    }
}
