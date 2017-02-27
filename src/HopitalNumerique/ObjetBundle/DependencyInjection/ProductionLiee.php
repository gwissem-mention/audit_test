<?php

namespace HopitalNumerique\ObjetBundle\DependencyInjection;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;

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
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ContenuManager ContenuManager
     */
    private $contenuManager;

    /**
     * Constructeur.
     */
    public function __construct(Entity $entity, ObjetManager $objetManager, ContenuManager $contenuManager)
    {
        $this->entity = $entity;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
    }

    /**
     * Reprise de formatteProductionsLiees().
     */
    public function getFormattedProductionsLiees($entity)
    {
        $formattedProductionsLiees = [];

        if ($entity instanceof Objet) {
            foreach ($this->objetManager->getProductionsLiees($entity) as $one) {
                $formattedProductionsLiees[] = $this->formatProductionsLiees($one);
            }
        }

        if (!is_null($entity->getObjets())) {
            foreach ($entity->getObjets() as $productionLieeString) {
                $productionLieeStringExplode = explode(':', $productionLieeString);
                $productionLieeType = $productionLieeStringExplode[0];
                $entityId = intval($productionLieeStringExplode[1]);

                switch ($productionLieeType) {
                    case 'PUBLICATION':
                    case 'ARTICLE':
                        $entity = $this->objetManager->findOneById($entityId);
                        break;
                    case 'INFRADOC':
                        $entity = $this->contenuManager->findOneById($entityId);
                        break;
                    default:
                        continue;
                }

                $formattedProductionsLiees[] = $this->formatProductionsLiees($entity);
            }
        }

        return $formattedProductionsLiees;
    }

    private function formatProductionsLiees($entity)
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
