<?php
namespace HopitalNumerique\RechercheBundle\Doctrine\Referencement;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
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
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement Referencement
     */
    private $referencement;

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
    public function __construct(RouterInterface $router, Referencement $referencement, CurrentDomaine $currentDomaine, ConnectedUser $connectedUser, EntityHasReferenceManager $entityHasReferenceManager, EntityHasNoteManager $entityHasNoteManager, ObjetManager $objetManager, ContenuManager $contenuManager)
    {
        $this->router = $router;
        $this->referencement = $referencement;
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
    private function getEntitiesPropertiesByReferenceIds(array $groupedReferenceIds = null, array $entityTypeIds = null, array $publicationCategoryIds = null, $resultFilters = [])
    {
        $currentDomaine = $this->currentDomaine->get();
        $entitiesProperties = $this->entityHasReferenceManager->getWithNotes($currentDomaine, $groupedReferenceIds, $this->connectedUser->get(), $entityTypeIds, $publicationCategoryIds, $resultFilters);
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

        $entitiesProperties = $this->getEntitiesPropertiesByReferenceIds($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);

        foreach ($entitiesProperties as $entityProperties) {
            $group = (null !== $entityProperties['objetPointDurTypeId'] ? 'points-durs' : 'productions');

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
                'entityType' => Entity::ENTITY_TYPE_OBJET,
                'entityId' => $objet->getId(),
                'url' => $this->router->generate('hopital_numerique_publication_publication_objet', ['id' => $objet->getId(), 'alias' => $objet->getAlias()]),
                'pertinenceNiveau' => null,
                'pointDur' => $objet->isPointDur(),
                'categoryIds' => $objet->getTypeIds(),
                'categoryLabels' => implode(' &diams; ', $objet->getTypeLabels())
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
                'entityType' => Entity::ENTITY_TYPE_CONTENU,
                'entityId' => $contenu->getId(),
                'url' => $this->router->generate('hopital_numerique_publication_publication_contenu', ['idc' => $contenu->getId(), 'aliasc' => $contenu->getAlias(), 'id' => $contenu->getObjet()->getId(), 'alias' => $contenu->getObjet()->getAlias()]),
                'pertinenceNiveau' => null,
                'pointDur' => $contenu->isPointDur(),
                'categoryIds' => (count($contenu->getTypes()) > 0 ? $contenu->getTypeIds() : $contenu->getObjet()->getTypeIds()),
                'categoryLabels' => $contenu->getTypeLabels()
            ];
        }

        return $entitiesProperties;
    }
}
