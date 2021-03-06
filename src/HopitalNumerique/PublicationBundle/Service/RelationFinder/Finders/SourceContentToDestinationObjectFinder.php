<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;

/**
 * Class SourceContentToDestinationContentFinder
 *
 * Retrieves objects related with current content (A1->B)
 */
class SourceContentToDestinationObjectFinder implements FinderInterface
{
    /**
     * @var ObjetRepository
     */
    protected $objectRepository;

    /**
     * @var ProductionLiee
     */
    protected $relatedResourceTransformer;

    /**
     * SourceContentToDestinationContentFinder constructor.
     *
     * @param ObjetRepository $objetRepository
     * @param ProductionLiee  $productionLiee
     */
    public function __construct(
        ObjetRepository $objetRepository,
        ProductionLiee $productionLiee
    ) {
        $this->objectRepository = $objetRepository;
        $this->relatedResourceTransformer = $productionLiee;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data)
    {
        if ($data instanceof Contenu) {
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
        /** @var Contenu $content */
        $content = $data;

        $relatedResources = [];

        if (is_null($content->getObjets())) {
            return [];
        }

        foreach ($content->getObjets() as $relatedObjectString) {
            $relatedObject = explode(':', $relatedObjectString);
            $relatedObjectType = $relatedObject[0];
            $relatedObjectId = $relatedObject[1];

            if ($relatedObjectType == 'ARTICLE' || $relatedObjectType == 'PUBLICATION') {
                $entity = $this->objectRepository->findOneBy(['id' => $relatedObjectId]);

                if (null !== $entity) {
                    $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees($entity);
                }
            }
        }

        return $relatedResources;
    }
}
