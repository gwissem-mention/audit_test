<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;

/**
 * Class DestinationObjectToSourceObjectFinder
 *
 * Retrieves contents related with current object (B->A)
 */
class DestinationObjectToSourceObjectFinder implements FinderInterface
{
    /**
     * @var ObjetManager
     */
    protected $objectManager;

    /**
     * @var ProductionLiee
     */
    protected $relatedResourceTransformer;

    /**
     * DestinationObjectToSourceObjectFinder constructor.
     *
     * @param ObjetManager   $objetManager
     * @param ProductionLiee $productionLiee
     */
    public function __construct(ObjetManager $objetManager, ProductionLiee $productionLiee)
    {
        $this->objectManager = $objetManager;
        $this->relatedResourceTransformer = $productionLiee;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data)
    {
        if ($data instanceof Objet) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function find($data)
    {
        /** @var Objet $object */
        $object = $data;

        $relatedResources = [];

        $relatedObjects = $this->objectManager->getObjectRelationships(
            $object
        );

        foreach ($relatedObjects as $relatedObject) {
            $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                $relatedObject
            );
        }

        return $relatedResources;
    }
}
