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
     * @var integer ID de la catégorie parente des références
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


    /**
     * Constructeur.
     */
    public function __construct(CurrentDomaine $currentDomaine, ReferenceManager $referenceManager)
    {
        $this->currentDomaine = $currentDomaine;
        $this->referenceManager = $referenceManager;
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
                'id' => 'pc-'.$productionCategory->getId(),
                'referenceId' => $productionCategory->getId(),
                'libelle' => $productionCategory->getLibelle()
            ];
        }

        $categoriesProperties[] = [
            'id' => 't-'.Entity::ENTITY_TYPE_FORUM_TOPIC,
            'entityType' => Entity::ENTITY_TYPE_FORUM_TOPIC,
            'libelle' => Entity::CATEGORY_FORUM_TOPIC_LABEL
        ];
        $categoriesProperties[] = [
            'id' => 't-'.Entity::ENTITY_TYPE_AMBASSADEUR,
            'entityType' => Entity::ENTITY_TYPE_AMBASSADEUR,
            'libelle' => Entity::CATEGORY_AMBASSADEUR_LABEL
        ];
        $categoriesProperties[] = [
            'id' => 't-'.Entity::ENTITY_TYPE_RECHERCHE_PARCOURS,
            'entityType' => Entity::ENTITY_TYPE_RECHERCHE_PARCOURS,
            'libelle' => Entity::CATEGORY_RECHERCHE_PARCOURS_LABEL
        ];
        $categoriesProperties[] = [
            'id' => 't-'.Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE,
            'entityType' => Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE,
            'libelle' => Entity::CATEGORY_COMMUNAUTE_PRATIQUES_GROUPE_LABEL
        ];

        return $categoriesProperties;
    }
}
