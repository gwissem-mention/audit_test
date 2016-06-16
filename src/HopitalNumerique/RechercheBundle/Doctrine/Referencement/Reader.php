<?php
namespace HopitalNumerique\RechercheBundle\Doctrine\Referencement;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;
use HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser;
use Symfony\Component\Routing\RouterInterface;

/**
 * Lecteur de la recherche par référencement.
 */
class Reader
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;

    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement Referencement
     */
    private $referencement;

    /**
     * @var \HopitalNumerique\RechercheBundle\Doctrine\Referencement\Modulation Modulation
     */
    private $modulation;

    /**
     * @var \HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;

    /**
     * @var \HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser ConnectedUser
     */
    private $connectedUser;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager EntityHasNoteManager
     */
    private $entityHasNoteManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ContenuManager ContenuManager
     */
    private $contenuManager;


    /**
     * @var boolean
     */
    private $isSearchedText = false;


    /**
     * Constructor.
     */
    public function __construct(RouterInterface $router, Entity $entity, Referencement $referencement, Modulation $modulation, CurrentDomaine $currentDomaine, ConnectedUser $connectedUser, EntityHasReferenceManager $entityHasReferenceManager, EntityHasNoteManager $entityHasNoteManager, ObjetManager $objetManager, ContenuManager $contenuManager)
    {
        $this->router = $router;
        $this->entity = $entity;
        $this->referencement = $referencement;
        $this->modulation = $modulation;
        $this->currentDomaine = $currentDomaine;
        $this->connectedUser = $connectedUser;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
        $this->entityHasNoteManager = $entityHasNoteManager;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
    }


    /**
     *
     * @param boolean $isSearchedText
     */
    public function setIsSearchedText($isSearchedText)
    {
        $this->isSearchedText = $isSearchedText;
    }


    /**
     * Retourne les entités.
     *
     * @param array<integer>|null $groupedReferenceIds    ID des références
     * @param array<integer>|null $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null $publicationCategoryIds ID des catégories de publications à récupérer
     * @param array               $resultFilters          Filtres à appliquer
     * @return array Entités
     */
    public function getEntitiesPropertiesByReferenceIds(array $groupedReferenceIds = null, array $entityTypeIds = null, array $publicationCategoryIds = null, $resultFilters = [])
    {
        $currentDomaine = $this->currentDomaine->get();

        $entitiesProperties = $this->entityHasReferenceManager->getWithNotes(
            $currentDomaine,
            $groupedReferenceIds,
            $this->connectedUser->get(),
            $entityTypeIds,
            $publicationCategoryIds,
            $resultFilters
        );

        if (!$this->isSearchedText) {
            usort($entitiesProperties, [$this, 'orderEntitiesProperties']);
        }

        return $entitiesProperties;
    }

    /**
     * Callable pour trier les EntitiesProperties.
     *
     * @param array $entityProperties1 EntityProperties 1
     * @param array $entityProperties2 EntityProperties 2
     */
    private function orderEntitiesProperties($entityProperties1, $entityProperties2)
    {
        if (intval($entityProperties1['primarySum']) > intval($entityProperties2['primarySum'])) {
            return -1;
        }
        if (intval($entityProperties1['primarySum']) < intval($entityProperties2['primarySum'])) {
            return 1;
        }

        if (intval($entityProperties1['referencesCount']) > intval($entityProperties2['referencesCount'])) {
            return -1;
        }
        if (intval($entityProperties1['referencesCount']) < intval($entityProperties2['referencesCount'])) {
            return 1;
        }

        if ($entityProperties1['note'] > $entityProperties2['note']) {
            return 1;
        }
        if ($entityProperties1['note'] < $entityProperties2['note']) {
            return -1;
        }

        if (intval($entityProperties1['avgObjetNote']) > intval($entityProperties2['avgObjetNote'])) {
            return 1;
        }
        if (intval($entityProperties1['avgObjetNote']) < intval($entityProperties2['avgObjetNote'])) {
            return -1;
        }

        return 0;
    }

    /**
     * Retourne les entités par groupe.
     *
     * @param array<integer>|null $groupedReferenceIds    ID des références
     * @param array<integer>|null $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null $publicationCategoryIds ID des catégories de publications à récupérer
     * @param array               $resultFilters          Filtres à appliquer
     * @return array Entités
     */
    public function getEntitiesPropertiesByReferenceIdsByGroup(array $groupedReferenceIds = null, array $entityTypeIds = null, array $publicationCategoryIds = null, array $resultFilters = [])
    {
        $entitiesPropertiesByGroup = [
            'points-durs' => [],
            'productions' => []
        ];

        if (null !== $groupedReferenceIds && 0 === count($groupedReferenceIds)) {
            return $entitiesPropertiesByGroup;
        }

        //<-- Transformation des ID en entier
        foreach ($groupedReferenceIds as $group => $referenceIds) {
            foreach ($referenceIds as $i => $referenceId) {
                $groupedReferenceIds[$group][$i] = intval($referenceId);
            }
        }
        //->

        $referencesTree = $this->referencement->getReferencesTree([$this->currentDomaine->get()], null, $this->currentDomaine->get()->getReferenceRoot());
        $referenceIds = $this->modulation->getModulatedReferenceIds(
            $this->referencement->getReferenceIdsByGroupedReferenceIds($groupedReferenceIds),
            $referencesTree
        );

        $groupedReferenceIds = $this->referencement->getReferenceIdsKeyedByGroup($referenceIds, $referencesTree);

        $entitiesProperties = $this->getEntitiesPropertiesByReferenceIds($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);

        foreach ($entitiesProperties as $entityProperties) {
            $entityPropertiesByGroup = [
                'entityType' => $entityProperties['entityType'],
                'entityId' => $entityProperties['entityId'],
                'pertinenceNiveau' => $this->referencement->getPertinenceNiveauByPrimaryAndNote($entityProperties['primarySum'], $entityProperties['note']),
                'categoryIds' => []
            ];

            foreach ($entityProperties['objetTypeIds'] as $objetTypeId) {
                if (!empty($objetTypeId)) {
                    $entityPropertiesByGroup['categoryIds'][] = $objetTypeId;
                }
            }
            foreach ($entityProperties['contenuObjetTypeIds'] as $objetTypeId) {
                if (!empty($objetTypeId)) {
                    $entityPropertiesByGroup['categoryIds'][] = $objetTypeId;
                }
            }

            $group = (in_array(Reference::CATEGORIE_OBJET_POINT_DUR_ID, $entityPropertiesByGroup['categoryIds']) ? 'points-durs' : 'productions');

            $entitiesPropertiesByGroup[$group][] = $entityPropertiesByGroup;
        }

        return $entitiesPropertiesByGroup;
    }

    /**
     * Retourne les entités d'objets.
     *
     * @param array<integer> $objetIds ID des objets
     * @return array Entités
     */
    public function getEntitiesPropertiesByObjetIds(array $objetIds)
    {
        $entitiesProperties = [];
        $objets = $this->objetManager->findBy(['id' => $objetIds]);

        foreach ($objets as $objet) {
            $entitiesProperties[] = [
                'title' => $this->entity->getTitleByEntity($objet),
                'subtitle' => $this->entity->getSubtitleByEntity($objet),
                'entityType' => Entity::ENTITY_TYPE_OBJET,
                'entityId' => $objet->getId(),
                'url' => $this->router->generate('hopital_numerique_publication_publication_objet', ['id' => $objet->getId(), 'alias' => $objet->getAlias()]),
                'pertinenceNiveau' => null,
                'pointDur' => $objet->isPointDur(),
                'categoryIds' => $this->entity->getCategoryIdsByEntity($objet),
                'categoryLabels' => $this->entity->getCategoryByEntity($objet)
            ];
        }

        return $entitiesProperties;
    }

    /**
     * Retourne les entités d'objets.
     *
     * @param array<integer> $contenuIds ID des objets
     * @return array Entités
     */
    public function getEntitiesPropertiesByContenuIds(array $contenuIds)
    {
        $entitiesProperties = [];
        $contenus = $this->contenuManager->findBy(['id' => $contenuIds]);

        foreach ($contenus as $contenu) {
            $entitiesProperties[] = [
                'title' => $this->entity->getTitleByEntity($contenu),
                'subtitle' => $this->entity->getSubtitleByEntity($contenu),
                'entityType' => Entity::ENTITY_TYPE_CONTENU,
                'entityId' => $contenu->getId(),
                'url' => $this->router->generate('hopital_numerique_publication_publication_contenu', ['idc' => $contenu->getId(), 'aliasc' => $contenu->getAlias(), 'id' => $contenu->getObjet()->getId(), 'alias' => $contenu->getObjet()->getAlias()]),
                'pertinenceNiveau' => null,
                'pointDur' => $contenu->isPointDur(),
                'categoryIds' => $this->entity->getCategoryIdsByEntity($contenu),
                'categoryLabels' => $this->entity->getCategoryByEntity($contenu)
            ];
        }

        return $entitiesProperties;
    }

    public function getEntitiesPropertiesKeyedByGroupForEntity($entity)
    {
        $referencesTree = $this->referencement->getReferencesTree([$this->currentDomaine->get()]);
        $entitiesHaveReferences = $this->entityHasReferenceManager->findBy([
            'entityId' => $this->entity->getEntityId($entity),
            'entityType' => $this->entity->getEntityType($entity)
        ]);

        $referenceIds = $this->entityHasReferenceManager->getReferenceIdsForEntitiesHaveReferences($entitiesHaveReferences);
        $referenceIdskeyedByGroup = $this->referencement->getReferenceIdskeyedByGroup($referenceIds, $referencesTree);

        return $this->getEntitiesPropertiesByReferenceIdsByGroup($referenceIdskeyedByGroup);
    }

    public function getEntitiesPropertiesKeyedByGroupByGroupedReferenceIds(array $groupedReferenceIds, array $entityTypeIds = null, array $publicationCategoryIds = null, array $resultFilters = [])
    {
        $entitiesPropertiesKeyedByGroup = [];
        $entitiesPropertiesByReferenceIdsByGroup = $this->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);

        foreach ($entitiesPropertiesByReferenceIdsByGroup as $group => $entitiesPropertiesByReferenceIds) {
            $entitiesPropertiesKeyedByGroup[$group] = [];

            $entitiesPropertiesByReferenceIdsByType = [];
            foreach ($entitiesPropertiesByReferenceIds as $entityProperties) {
                if (!array_key_exists($entityProperties['entityType'], $entitiesPropertiesByReferenceIdsByType)) {
                    $entitiesPropertiesByReferenceIdsByType[$entityProperties['entityType']] = [];
                }
                $entitiesPropertiesByReferenceIdsByType[$entityProperties['entityType']][$entityProperties['entityId']] = $entityProperties;
                //$entitiesPropertiesKeyedByGroup[$group][$entityProperties['entityId']] = [];
            }

            foreach ($entitiesPropertiesByReferenceIdsByType as $entityType => $entitiesProperties) {
                $entities = $this->entity->getEntitiesByTypeAndIds($entityType, array_keys($entitiesProperties));
                foreach ($entities as $entity) {
                    $entityId = $this->entity->getEntityId($entity);
                    $entityProperties = [
                        'entityId' => $entityId,
                        'entityType' => $entityType,
                        'title' => $this->entity->getTitleByEntity($entity),
                        'subtitle' => $this->entity->getSubtitleByEntity($entity),
                        'url' => $this->entity->getFrontUrlByEntity($entity),
                        'description' => $this->entity->getDescriptionByEntity($entity),
                        'category' => $this->entity->getCategoryByEntity($entity),
                        'pertinenceNiveau' => $entitiesProperties[$entityId]['pertinenceNiveau']
                    ];
                    $entitiesPropertiesKeyedByGroup[$group][] = $entityProperties;
                    /*$entitiesPropertiesKeyedByGroup[$group][$entityId]['entityId'] = $entityId;
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['entityType'] = $entityType;
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['title'] = $this->entity->getTitleByEntity($entity);
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['subtitle'] = $this->entity->getSubtitleByEntity($entity);
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['url'] = $this->entity->getFrontUrlByEntity($entity);
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['description'] = $this->entity->getDescriptionByEntity($entity);
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['category'] = $this->entity->getCategoryByEntity($entity);
                    $entitiesPropertiesKeyedByGroup[$group][$entityId]['pertinenceNiveau'] = $entitiesProperties[$entityId]['pertinenceNiveau'];*/
                }
            }
        }

        return $entitiesPropertiesKeyedByGroup;
    }

    public function getRelatedObjectsByType($entity, $type)
    {
        $entityType = $this->entity->getEntityType($entity);
        $entityId = $this->entity->getEntityId($entity);
        $referenceIds = $this->entityHasReferenceManager
            ->getReferenceIdsByEntityTypeAndEntityId($entityType, $entityId);

        $related = [];
        $relatedsAttributes = $this->getEntitiesPropertiesByReferenceIds([$referenceIds], [$type]);
        foreach ($relatedsAttributes as $relatedAttributes) {
            $current = $this->entity->getEntityByTypeAndId(
                $relatedAttributes['entityType'],
                $relatedAttributes['entityId']
            );
            $related[] = [
                'title' => $this->entity->getTitleByEntity($current),
                'subtitle' => $this->entity->getSubtitleByEntity($current),
                'category' => $this->entity->getCategoryByEntity($current),
                'description' => $this->entity->getDescriptionByEntity($current),
                'url' => $this->entity->getFrontUrlByEntity($current),
            ];
        }

        return $related;
    }
}
