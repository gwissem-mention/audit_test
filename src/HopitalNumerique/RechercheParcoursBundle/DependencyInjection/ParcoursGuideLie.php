<?php
namespace HopitalNumerique\RechercheParcoursBundle\DependencyInjection;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\RechercheBundle\Doctrine\Referencement\Reader as ReferencementReader;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;
use HopitalNumerique\RechercheParcoursBundle\Manager\RechercheParcoursManager;

/**
 * Gestion des démarches liées.
 */
class ParcoursGuideLie
{
    /**
     * @var integer Nombre de caractères max pour le titre
     */
    const TITLE_MAXLENGTH = 100;

    /**
     * @var integer Nombre de caractères max pour la description
     */
    const DESCRIPTION_MAXLENGTH = 100;


    /**
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;

    /**
     * @var \HopitalNumerique\RechercheBundle\Doctrine\Referencement\Reader ReferencementReader
     */
    private $referencementReader;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;

    /**
     * @var \HopitalNumerique\RechercheParcoursBundle\Manager\RechercheParcoursManager RechercheParcoursManager
     */
    private $rechercheParcoursManager;


    /**
     * Constructeur.
     */
    public function __construct(Entity $entity, ReferencementReader $referencementReader, EntityHasReferenceManager $entityHasReferenceManager, RechercheParcoursManager $rechercheParcoursManager)
    {
        $this->entity = $entity;
        $this->referencementReader = $referencementReader;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
        $this->rechercheParcoursManager = $rechercheParcoursManager;
    }


    /**
     * Retourne les parcours guidés formatés.
     *
     * @return array Parcours guidés
     */
    public function getFormattedParcoursGuidesLies($entity)
    {
        $entityType = $this->entity->getEntityType($entity);
        $entityId = $this->entity->getEntityId($entity);
        $referenceIds = $this->entityHasReferenceManager->getReferenceIdsByEntityTypeAndEntityId($entityType, $entityId);

        $parcoursGuidesProperties = $this->referencementReader->getEntitiesPropertiesByReferenceIds([$referenceIds], [Entity::ENTITY_TYPE_RECHERCHE_PARCOURS]);

        $parcoursGuideIds = [];
        foreach ($parcoursGuidesProperties as $parcoursGuideProperties) {
            $parcoursGuideIds[] = $parcoursGuideProperties['entityId'];
        }
        $parcoursGuides = $this->rechercheParcoursManager->findBy(['id' => $parcoursGuideIds]);


        $formattedParcoursGuides = [];

        foreach ($parcoursGuides as $parcoursGuide) {
            $title = $this->entity->getTitleByEntity($parcoursGuide, self::TITLE_MAXLENGTH);
            $description = $this->entity->getDescriptionByEntity($parcoursGuide, self::DESCRIPTION_MAXLENGTH);

            $formattedParcoursGuides[] = [
                'title' => $title,
                'subtitle' => $this->entity->getSubtitleByEntity($parcoursGuide),
                'category' => $this->entity->getCategoryByEntity($parcoursGuide),
                'description' => $description,
                'url' => $this->entity->getFrontUrlByEntity($parcoursGuide)
            ];
        }

        return $formattedParcoursGuides;
    }
}
