<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchConfigPublicationType;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\EditGuidedSearchConfigCommand;

class EditGuidedSearchConfigCommandFactory
{
    /**
     * @var ReferenceRepository $referenceRepository
     */
    protected $referenceRepository;

    /**
     * EditGuidedSearchConfigCommandHydrator constructor.
     * @param ReferenceRepository $referenceRepository
     */
    public function __construct(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * @param RechercheParcoursGestion $rechercheParcoursGestion
     *
     * @return EditGuidedSearchConfigCommand
     */
    public function createFromEntity(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $editRechercheParcoursGestionCommand = new EditGuidedSearchConfigCommand();
        $editRechercheParcoursGestionCommand->rechercheParcoursGestionId = $rechercheParcoursGestion->getId();
        $editRechercheParcoursGestionCommand->name = $rechercheParcoursGestion->getNom();
        $editRechercheParcoursGestionCommand->domaines = $rechercheParcoursGestion->getDomaines();
        $editRechercheParcoursGestionCommand->referencesParentes = $rechercheParcoursGestion->getReferencesParentes();
        $editRechercheParcoursGestionCommand->referencesVentilations = $rechercheParcoursGestion->getReferencesVentilations();
        $editRechercheParcoursGestionCommand->update = true;

        $this->hydratePublicationsTypes($editRechercheParcoursGestionCommand, $rechercheParcoursGestion);

        return $editRechercheParcoursGestionCommand;
    }

    /**
     * @return EditGuidedSearchConfigCommand
     */
    public function createEmpty()
    {
        $editRechercheParcoursGestionCommand = new EditGuidedSearchConfigCommand();

        $this->hydratePublicationsTypes($editRechercheParcoursGestionCommand);

        return $editRechercheParcoursGestionCommand;
    }

    private function hydratePublicationsTypes(
        EditGuidedSearchConfigCommand $editRechercheParcoursGestionCommand,
        RechercheParcoursGestion $rechercheParcoursGestion = null
    ) {
        foreach (GuidedSearchConfigPublicationType::getTypes() as $type) {
            $editRechercheParcoursGestionCommand->publicationsType[] = $this->getRechercheParcoursgestionPublicationType($type, $rechercheParcoursGestion);
        }

        usort($editRechercheParcoursGestionCommand->publicationsType, function ($a, $b) {
            if ($a['order'] == $b['order']) {
                return 0;
            }

            return ($a['order'] < $b['order']) ? -1 : 1;
        });
    }

    /**
     * @param string $type
     * @param RechercheParcoursGestion|null $rechercheParcoursGestion$type
     *
     * @return array
     */
    private function getRechercheParcoursgestionPublicationType($type, RechercheParcoursGestion $rechercheParcoursGestion = null)
    {
        $default = [
            'slug' => $type,
            'order' => 0,
            'active' => false,
        ];

        if (is_null($rechercheParcoursGestion)) {
            return $default;
        }

        /** @var GuidedSearchConfigPublicationType $publicationType */
        foreach ($rechercheParcoursGestion->getPublicationsType() as $publicationType) {
            if ($publicationType->getType() === $type) {
                return array_merge($default, [
                    'order' => $publicationType->getOrder(),
                    'active' => $publicationType->isActive(),
                ]);
            }
        }

        $default['order'] = $rechercheParcoursGestion->getPublicationsType()->count() + 1;

        return $default;
    }
}
