<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;

/**
 * Class SourceObjectToDestinationContentFinder
 *
 * Retrieves contents related with current object (A->B1)
 */
class SourceObjectToDestinationContentFinder implements FinderInterface
{
    /**
     * @var ContenuManager
     */
    protected $contentManager;

    /**
     * @var ProductionLiee
     */
    protected $relatedResourceTransformer;

    /**
     * SourceObjectToDestinationContentFinder constructor.
     *
     * @param ContenuManager $contenuManager
     * @param ProductionLiee $productionLiee
     */
    public function __construct(ContenuManager $contenuManager, ProductionLiee $productionLiee)
    {
        $this->contentManager = $contenuManager;
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

                if ($relatedObjectType == 'INFRADOC') {
                    $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                        $this->contentManager->findOneById(
                            $relatedObjectId
                        )
                    );
                }
            }
        }

        return $relatedResources;
    }
}
