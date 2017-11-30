<?php

namespace HopitalNumerique\ObjetBundle\DependencyInjection;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;

/**
 * Gestion des productions liées (pour Objet et Contenu).
 */
class ProductionLiee
{
    /**
     * @var int Nombre de caractères max pour le titre
     */
    const TITLE_MAXLENGTH = 100;

    /**
     * @var int Nombre de caractères max pour la description
     */
    const DESCRIPTION_MAXLENGTH = 100;

    /**
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;

    /**
     * Constructeur.
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    public function formatProductionsLiees($entity)
    {
        return [
            'title' => $this->entity->getTitleByEntity($entity, self::TITLE_MAXLENGTH),
            'subtitle' => $this->entity->getSubtitleByEntity($entity),
            'category' => $this->entity->getCategoryByEntity($entity),
            'description' => null,
            'url' => $this->entity->getFrontUrlByEntity($entity),
        ];
    }
}
