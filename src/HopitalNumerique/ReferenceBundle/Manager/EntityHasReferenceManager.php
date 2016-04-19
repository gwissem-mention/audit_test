<?php
namespace HopitalNumerique\ReferenceBundle\Manager;

use Doctrine\Common\Collections\Collection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité EntityHasReference.
 */
class EntityHasReferenceManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ReferenceBundle\Entity\EntityHasReference';


    /**
     * Retourne un arbre de toutes les EntityHasReference classées par type puis par ID d'entité.
     *
     * @return array Arbre
     */
    public function getAllClassifiedByEntityTypeClassifiedByEntityId()
    {
        $allClassifiedByEntityTypeClassifiedByEntityId = [];

        foreach ($this->findAll() as $entityHasReference) {
            $entityType = $entityHasReference->getEntityType();
            $entityId = $entityHasReference->getEntityId();

            if (!array_key_exists($entityType, $allClassifiedByEntityTypeClassifiedByEntityId)) {
                $allClassifiedByEntityTypeClassifiedByEntityId[$entityType] = [];
            }
            if (!array_key_exists($entityId, $allClassifiedByEntityTypeClassifiedByEntityId[$entityType])) {
                $allClassifiedByEntityTypeClassifiedByEntityId[$entityType][$entityId] = [];
            }

            $allClassifiedByEntityTypeClassifiedByEntityId[$entityType][$entityId][] = $entityHasReference;
        }

        return $allClassifiedByEntityTypeClassifiedByEntityId;
    }

    /**
     * Retourne les EntityHasReference par type d'entité, ID d'entité et domaines.
     *
     * @param integer $entityType Type d'entité
     * @param integer $entityId ID de l'entité
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\EntityHasReference> EntitiesHasReference
     */
    public function findByEntityTypeAndEntityIdAndDomaines($entityType, $entityId, $domaines)
    {
        return $this->getRepository()->findByEntityTypeAndEntityIdAndDomaines($entityType, $entityId, ($domaines instanceof Collection ? $domaines->toArray() : $domaines));
    }

    /**
     * Retourne les EntityHasReference avec leur note.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param array $references Références
     * @return array EntitiesHasReference
     */
    public function getWithNotes(Domaine $domaine, array $references)
    {
        return $this->getRepository()->getWithNotes($domaine, $references);
    }

    /**
     * Retourne toutes les entités (couples type + id).
     *
     * @return array Entités
     */
    public function getAllDistinctEntityTypesAndIds()
    {
        return $this->getRepository()->getAllDistinctEntityTypesAndIds();
    }
}
