<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchConfigPublicationType;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursGestionRepository;

class EditGuidedSearchConfigHandler
{
    /**
     * @var RechercheParcoursGestionRepository $rechercheParcoursGestionRepository
     */
    protected $rechercheParcoursGestionRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ReferenceRepository $referenceRepository
     */
    protected $referenceRepository;

    /**
     * EditGuidedSearchConfigHandler constructor.
     *
     * @param RechercheParcoursGestionRepository $rechercheParcoursGestionRepository
     * @param EntityManagerInterface $entityManager
     * @param ReferenceRepository $referenceRepository
     */
    public function __construct(
        RechercheParcoursGestionRepository $rechercheParcoursGestionRepository,
        EntityManagerInterface $entityManager,
        ReferenceRepository $referenceRepository
    )
    {
        $this->rechercheParcoursGestionRepository = $rechercheParcoursGestionRepository;
        $this->entityManager = $entityManager;
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * @param EditGuidedSearchConfigCommand $command
     *
     * @return RechercheParcoursGestion
     */
    public function handle(EditGuidedSearchConfigCommand $command)
    {
        if (is_null($command->rechercheParcoursGestionId)) {
            $rechercheParcoursGestion = new RechercheParcoursGestion();
            $this->entityManager->persist($rechercheParcoursGestion);
        } else {
            $rechercheParcoursGestion = $this->rechercheParcoursGestionRepository->find($command->rechercheParcoursGestionId);
        }

        $rechercheParcoursGestion
            ->setNom($command->name)
            ->setDomaines($rechercheParcoursGestion->getDomaines())
            ->setReferencesVentilations($command->referencesVentilations)
            ->setReferencesParentes($command->referencesParentes)
        ;

        $this->handleParentsReferences($rechercheParcoursGestion);
        $this->handlePublicationsTypes($command, $rechercheParcoursGestion);

        $this->entityManager->flush();

        return $rechercheParcoursGestion;
    }

    /**
     * @param EditGuidedSearchConfigCommand $command
     * @param RechercheParcoursGestion $rechercheParcoursGestion
     */
    private function handlePublicationsTypes(EditGuidedSearchConfigCommand $command, RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $publicationsTypes = GuidedSearchConfigPublicationType::getTypes();

        $toRemove = $rechercheParcoursGestion->getPublicationsType()->filter(function (GuidedSearchConfigPublicationType $publicationType) use ($publicationsTypes) {
            return !isset($publicationsTypes[$publicationType->getType()]);
        });
        foreach ($toRemove as $publicationTypeToRemove) {
            $this->entityManager->remove($publicationTypeToRemove);
        }

        foreach ($command->publicationsType as $commandPublicationType) {
            $publicationType = $rechercheParcoursGestion->getPublicationsType()->filter(function (GuidedSearchConfigPublicationType $publicationType) use ($commandPublicationType) {
                return $publicationType->getType() === $commandPublicationType['slug'];
            })->first();

            if (!$publicationType) {
                $publicationType = new GuidedSearchConfigPublicationType();
                $publicationType
                    ->setType($publicationsTypes[$commandPublicationType['slug']])
                    ->setGuidedSearchConfig($rechercheParcoursGestion)
                ;
                $this->entityManager->persist($publicationType);
            }

            $publicationType
                ->setActive($commandPublicationType['active'])
                ->setOrder($commandPublicationType['order'])
            ;
        }
    }

    /**
     * @param RechercheParcoursGestion $rechercheParcoursGestion
     */
    private function handleParentsReferences(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $rechercheParcoursNew = [];
        $recherchesParcours = clone $rechercheParcoursGestion->getRechercheParcours();

        foreach ($rechercheParcoursGestion->getReferencesParentes() as $parentReference) {
            if (!$recherchesParcours->contains($parentReference)) {
                $rechercheParcours = new RechercheParcours();
                $rechercheParcours
                    ->setReference($parentReference)
                    ->setRecherchesParcoursGestion($rechercheParcoursGestion)
                    ->setOrder(count($rechercheParcoursNew) + 1)
                ;

                $rechercheParcoursNew[] = $rechercheParcours;
            }
        }

        foreach ($recherchesParcours as $parcours) {
            if (!$rechercheParcoursGestion->getReferencesParentes()->contains($parcours->getReference())) {
                $this->entityManager->remove($parcours);
            }
        }
    }
}
