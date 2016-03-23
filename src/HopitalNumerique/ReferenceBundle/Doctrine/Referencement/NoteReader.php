<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Referencement;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement\Entity as ReferencementEntityService;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;

/**
 * Service lisant les notes de référencement.
 */
class NoteReader
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement\Entity ReferencementEntityService
     */
    private $referencementEntityService;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager EntityHasNoteManager
     */
    private $entityHasNoteManager;


    /**
     * Constructeur.
     */
    public function __construct(ReferencementEntityService $referencementEntityService, EntityHasNoteManager $entityHasNoteManager)
    {
        $this->referencementEntityService = $referencementEntityService;
        $this->entityHasNoteManager = $entityHasNoteManager;
    }


    /**
     * Retourne la note telle qu'elle peut être affichée.
     *
     * @param object                                         $entity  Entité
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return string Note
     */
    public function getNoteByEntityAndDomaineForAffichage($entity, Domaine $domaine)
    {
        $entityType = $this->referencementEntityService->getEntityType($entity);
        $entityId = $this->referencementEntityService->getEntityId($entity);

        return $this->getNoteByEntityTypeAndEntityIdAndDomaineForAffichage($entityType, $entityId, $domaine);
    }

    /**
     * Retourne la note telle qu'elle peut être affichée.
     *
     * @param string                                         $entityType Type d'entité
     * @param integer                                        $entityId   ID d'entité
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine    Domaine
     * @return string Note
     */
    private function getNoteByEntityTypeAndEntityIdAndDomaineForAffichage($entityType, $entityId, Domaine $domaine)
    {
        $entityHasNote = $this->entityHasNoteManager->findOneBy([
            'entityType' => $entityType,
            'entityId' => $entityId,
            'domaine' => $domaine
        ]);

        return (null !== $entityHasNote ? str_replace(',00', '', number_format($entityHasNote->getNote(), 2, ',', ' ')) : '-');
    }
}
