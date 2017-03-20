<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;

/**
 * Class SourceContentToDestinationContentFinder
 */
class DestinationObjectToSourceContent implements FinderInterface
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
     * SourceContentToDestinationContentFinder constructor.
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

        $contents = $this->contentManager->findAll();

        /** @var Contenu $content */
        foreach ($contents as $content) {
            if (!is_null($content->getObjets())) {
                foreach ($content->getObjets() as $relatedObjectString) {
                    $relatedObject = explode(':', $relatedObjectString);
                    $relatedObjectType = $relatedObject[0];
                    $relatedObjectId = $relatedObject[1];

                    if (($relatedObjectType == 'PUBLICATION'
                        || $relatedObjectType == 'ARTICLE')
                           && $relatedObjectId == $object->getId()
                    ) {
                        $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                            $this->contentManager->findOneById(
                                $content->getId()
                            )
                        );
                    }
                }
            }
        }

        return $relatedResources;
    }
}
