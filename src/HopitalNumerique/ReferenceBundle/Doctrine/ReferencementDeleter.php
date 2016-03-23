<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine;

use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement\Entity as ReferencementEntity;
use HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteDeleter;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Service supprimant le référencement.
 */
class ReferencementDeleter
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement\Entity ReferencementEntity
     */
    private $referencementEntity;

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
    public function __construct(ReferencementEntity $referencementEntity, NoteDeleter $noteDeleter, EntityHasReferenceManager $entityHasReferenceManager)
    {
        $this->referencementEntity = $referencementEntity;
        $this->noteDeleter = $noteDeleter;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
    }


    /**
     * Supprime les éventuelles références de l'ambassadeur si l'utilisateur ne l'est plus.
     */
    public function removeAmbassadeurReferences(User $user)
    {
        if (!$user->hasRoleAmbassadeur()) {
            $this->removeReferencesByEntityTypeAndEntityId(
                ReferencementEntity::ENTITY_TYPE_AMBASSADEUR,
                $user->getId()
            );
        }
    }

    /**
     * Supprime les références d'un objet.
     */
    public function removeReferencesByEntity($entity)
    {
        $this->removeReferencesByEntityTypeAndEntityId(
            $this->referencementEntity->getEntityType($entity),
            $this->referencementEntity->getEntityId($entity)
        );
    }

    /**
     * Supprime les références d'un objet.
     */
    private function removeReferencesByEntityTypeAndEntityId($entityType, $entityId)
    {
        $entityHasReferences = $this->entityHasReferenceManager->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId
        ]);

        if (count($entityHasReferences) > 0) {
            $this->entityHasReferenceManager->delete($entityHasReferences);
            $this->noteDeleter->removeByEntityTypeAndEntityId($entityType, $entityId);
        }
    }
}
