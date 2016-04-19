<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Referencement;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteDeleter;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;

/**
 * Service supprimant les référencements inexistants.
 */
class Deleter
{
    /**
     * @var ^HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteDeleter NoteDeleter
     */
    private $noteDeleter;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;


    /**
     * Constructeur.
     */
    public function __construct(Entity $entity, NoteDeleter $noteDeleter, EntityHasReferenceManager $entityHasReferenceManager)
    {
        $this->entity = $entity;
        $this->noteDeleter = $noteDeleter;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
    }


    /**
     * Supprime le référencement correspondant à des entités inexistantes.
     */
    public function removeInexistants()
    {
        $entitiesHasReference = $this->entityHasReferenceManager->getAllDistinctEntityTypesAndIds();

        foreach ($entitiesHasReference as $entityHasReference) {
            $entityType = $entityHasReference['entityType'];
            $entityId = $entityHasReference['entityId'];
            $entity = $this->entity->getEntityByTypeAndId($entityType, $entityId);

            if (null === $entity) {
                $this->noteDeleter->removeByEntityTypeAndEntityId($entityType, $entityId);
                $this->entityHasReferenceManager->delete($entitiesHasReference);
            }
        }
    }
}
