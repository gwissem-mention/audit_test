<?php
namespace HopitalNumerique\ReferenceBundle\Manager;

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
}
