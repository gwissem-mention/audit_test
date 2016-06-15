<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Referencement;

use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;

/**
 * Service supprimant les notes de référencement.
 */
class NoteDeleter
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager EntityHasNoteManager
     */
    private $entityHasNoteManager;


    /**
     * Constructeur.
     */
    public function __construct(EntityHasNoteManager $entityHasNoteManager)
    {
        $this->entityHasNoteManager = $entityHasNoteManager;
    }


    /**
     * Supprime les références d'un objet.
     */
    public function removeByEntityTypeAndEntityId($entityType, $entityId)
    {
        $entityHasNotes = $this->entityHasNoteManager->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId
        ]);

        if (count($entityHasNotes) > 0) {
            $this->entityHasNoteManager->delete($entityHasNotes);
        }
    }
}
