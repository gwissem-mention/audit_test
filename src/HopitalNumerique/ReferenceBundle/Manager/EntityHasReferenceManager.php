<?php
namespace HopitalNumerique\ReferenceBundle\Manager;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\RoleBundle\Manager\RoleManager;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité EntityHasReference.
 */
class EntityHasReferenceManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ReferenceBundle\Entity\EntityHasReference';

    /**
     * @var \Nodevo\RoleBundle\Manager\RoleManager RoleManager
     */
    private $roleManager;


    /**
     * Constructeur.
     */
    public function __construct(EntityManager $em, RoleManager $roleManager)
    {
        parent::__construct($em);

        $this->roleManager = $roleManager;
    }


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
     * @param array|null          $groupedReferences      Références
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    User
     * @param array<integer>|null $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null $publicationCategoryIds ID des catégories de publications à récupérer
     * @param array               $resultFilters          Filtres à appliquer (objetIds, contenuIds, primary)
     * @return array EntitiesHasReference
     */
    public function getWithNotes(Domaine $domaine, array $groupedReferences = null, User $user = null, array $entityTypeIds = null, array $publicationCategoryIds = null, $resultFilters = [])
    {
        $userRole = null;
        if (null !== $user) {
            $userRoles = $this->roleManager->findByUser($user);
            if (count($userRoles) > 0) {
                $userRole = $userRoles[0];
            }
        }

        $entitiesHaveReferences = [];
        $entitiesHaveReferencesWithoutRoles = $this->getRepository()->getWithNotes(
            $domaine,
            $groupedReferences,
            $entityTypeIds,
            $publicationCategoryIds,
            $resultFilters
        );

        $entitiesMatchProperties = $this->getRepository()->getMatchProperties($groupedReferences, $entityTypeIds);

        // Prise en compte des rôles utilisateur
        foreach ($entitiesHaveReferencesWithoutRoles as $entityHaveReferenceWithoutRoles) {
            $objetRoleIds = ('' != $entityHaveReferenceWithoutRoles['objetRoleIds'] ? explode(',', $entityHaveReferenceWithoutRoles['objetRoleIds']) : []);
            $contenuObjetRoleIds = ('' != $entityHaveReferenceWithoutRoles['contenuObjetRoleIds'] ? explode(',', $entityHaveReferenceWithoutRoles['contenuObjetRoleIds']) : []);

            $entityValid = (
                null === $userRole
                && (
                    0 === count($objetRoleIds)
                    && 0 === count($contenuObjetRoleIds)
                )
            )
            || (
                null !== $userRole
                && (
                    (0 === count($objetRoleIds) || !in_array($userRole->getId(), $objetRoleIds))
                    && (0 === count($contenuObjetRoleIds) || !in_array($userRole->getId(), $contenuObjetRoleIds))
                )
            );
            $entityHaveReferenceWithoutRoles = $this->addEntityMatchProperties($entityHaveReferenceWithoutRoles, $entitiesMatchProperties);
            $entityValid = $entityValid
                    // Si objet, vérifier si objet valide
                    && ($entityHaveReferenceWithoutRoles['entityType'] !== Entity::ENTITY_TYPE_OBJET || null !== $entityHaveReferenceWithoutRoles['objetId'])
                    // Vérifier si filtre primary
                    && (!array_key_exists('primary', $resultFilters) || (($resultFilters['primary'] && $entityHaveReferenceWithoutRoles['primarySum'] >= 1) || (!$resultFilters['primary'] && $entityHaveReferenceWithoutRoles['primarySum'] < 1)))
                ;
            if ($entityValid) {
                $entityHaveReferenceWithoutRoles['objetTypeIds'] = ('' != $entityHaveReferenceWithoutRoles['objetTypeIds'] ? array_values(array_unique(explode(',', $entityHaveReferenceWithoutRoles['objetTypeIds']))) : []);
                $entityHaveReferenceWithoutRoles['contenuObjetTypeIds'] = ('' != $entityHaveReferenceWithoutRoles['contenuObjetTypeIds'] ? array_values(array_unique(explode(',', $entityHaveReferenceWithoutRoles['contenuObjetTypeIds']))) : []);

                $entitiesHaveReferences[] = $entityHaveReferenceWithoutRoles;
            }
        }

        return $entitiesHaveReferences;
    }

    private function addEntityMatchProperties($entityHaveReferenceProperties, $entitiesMatchProperties)
    {
        $entityHaveReferenceProperties['referencesCount'] = 0;
        $entityHaveReferenceProperties['primarySum'] = 0;

        foreach ($entitiesMatchProperties as $existingEntityMatchProperties) {
            if ($existingEntityMatchProperties['entityType'] == $entityHaveReferenceProperties['entityType'] && $existingEntityMatchProperties['entityId'] == $entityHaveReferenceProperties['entityId']) {
                $entityHaveReferenceProperties['referencesCount'] = $existingEntityMatchProperties['referencesCount'];
                $entityHaveReferenceProperties['primarySum'] = $existingEntityMatchProperties['primarySum'];
            }
        }

        return $entityHaveReferenceProperties;
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

    /**
     * @return array<integer>
     */
    public function getReferenceIdsForEntitiesHaveReferences($entitiesHaveReferences)
    {
        $referenceIds = [];

        foreach ($entitiesHaveReferences as $entityHasReference) {
            $referenceIds[] = $entityHasReference->getReference()->getId();
        }

        return $referenceIds;
    }

    /**
     * @return array<integer>
     */
    public function getReferenceIdsByEntityTypeAndEntityId($entityType, $entityId)
    {
        $referenceIds = [];
        $entityHasReferences = $this->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId
        ]);

        foreach ($entityHasReferences as $entityHasReference) {
            $referenceIds[] = $entityHasReference->getReference()->getId();
        }

        return $referenceIds;
    }
}
