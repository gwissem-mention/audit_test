<?php

namespace HopitalNumerique\RechercheBundle\Doctrine\Referencement;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Gestion des catégories des entités de référencement.
 */
class Category
{
    /**
     * @var int ID de la catégorie parente des références
     */
    const REFERENCE_CATEGORY_PARENT_ID = 175;

    /**
     * @var \HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    private $refForumTopicId;

    private $refAmbassadeurId;

    private $refRechercheParcoursId;

    private $refComPratiqueId;

    private $refExpressionBesoinReponseId;

    /**
     * Constructeur.
     */
    public function __construct(CurrentDomaine $currentDomaine, ReferenceManager $referenceManager, $refForumTopicId, $refAmbassadeurId, $refRechercheParcoursId, $refComPratiqueId, $refExpressionBesoinReponseId)
    {
        $this->currentDomaine = $currentDomaine;
        $this->referenceManager = $referenceManager;
        $this->referenceManager = $referenceManager;
        $this->refForumTopicId = $refForumTopicId;
        $this->refAmbassadeurId = $refAmbassadeurId;
        $this->refRechercheParcoursId = $refRechercheParcoursId;
        $this->refComPratiqueId = $refComPratiqueId;
        $this->refExpressionBesoinReponseId = $refExpressionBesoinReponseId;
    }

    /**
     * Retourne les propriétés des catégories pour le référencement.
     *
     * @return array Category properties
     */
    public function getCategoriesProperties()
    {
        $categoriesProperties = [];

        $productionCategories = $this->referenceManager->getRefsByDomaineByParent(self::REFERENCE_CATEGORY_PARENT_ID, $this->currentDomaine->get()->getId());
        foreach ($productionCategories as $productionCategory) {
            $categoriesProperties[] = [
                'id' => 'pc-' . $productionCategory->getId(),
                'referenceId' => $productionCategory->getId(),
                'libelle' => $productionCategory->getLibelle(),
                'order' => $productionCategory->getOrder(),
            ];
        }

        $reference = $this->referenceManager->findOneById($this->refForumTopicId);

        if (!is_null($reference)) {
            $categoriesProperties[] = [
                'id' => 't-' . Entity::ENTITY_TYPE_FORUM_TOPIC,
                'entityType' => Entity::ENTITY_TYPE_FORUM_TOPIC,
                'libelle' => $this->referenceManager->findOneById($this->refForumTopicId)->getLibelle(),
                'order' => $this->referenceManager->findOneById($this->refForumTopicId)->getOrder(),
            ];
            $categoriesProperties[] = [
                'id' => 't-' . Entity::ENTITY_TYPE_AMBASSADEUR,
                'entityType' => Entity::ENTITY_TYPE_AMBASSADEUR,
                'libelle' => $this->referenceManager->findOneById($this->refAmbassadeurId)->getLibelle(),
                'order' => $this->referenceManager->findOneById($this->refAmbassadeurId)->getOrder(),
            ];
            $categoriesProperties[] = [
                'id' => 't-' . Entity::ENTITY_TYPE_RECHERCHE_PARCOURS,
                'entityType' => Entity::ENTITY_TYPE_RECHERCHE_PARCOURS,
                'libelle' => $this->referenceManager->findOneById($this->refRechercheParcoursId)->getLibelle(),
                'order' => $this->referenceManager->findOneById($this->refRechercheParcoursId)->getOrder(),
            ];
            $categoriesProperties[] = [
                'id' => 't-' . Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE,
                'entityType' => Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE,
                'libelle' => $this->referenceManager->findOneById($this->refComPratiqueId)->getLibelle(),
                'order' => $this->referenceManager->findOneById($this->refComPratiqueId)->getOrder(),
            ];
        }

        usort($categoriesProperties, function ($a, $b) {
            return $a['order'] > $b['order'];
        });

        return $categoriesProperties;
    }
}
