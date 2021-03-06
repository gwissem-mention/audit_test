<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;

/**
 * Class SourceObjectToDestinationObjectFinder
 *
 * Retrieves objects related with current object (A->B)
 */
class SourceObjectToDestinationObjectFinder implements FinderInterface
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
     * SourceObjectToDestinationObjectFinder constructor.
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

        if (!is_null($object->getObjets())) {
            foreach ($object->getObjets() as $relatedObjectString) {
                $relatedObject = explode(':', $relatedObjectString);
                $relatedObjectType = $relatedObject[0];
                $relatedObjectId = $relatedObject[1];

                if ($relatedObjectType == 'ARTICLE' || $relatedObjectType == 'PUBLICATION') {
                    $entity = $this->objectManager->findOneById($relatedObjectId);

                    if (null !== $entity) {
                        $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                            $entity
                        );
                    }
                }
            }
        }

        return $relatedResources;
    }
}
