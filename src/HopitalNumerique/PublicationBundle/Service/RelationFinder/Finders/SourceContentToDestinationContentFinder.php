<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;

/**
 * Class SourceContentToDestinationContentFinder
 *
 * Retrieves contents related with current content (A1->B1)
 */
class SourceContentToDestinationContentFinder implements FinderInterface
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

        if (!is_null($content->getObjets())) {
            foreach ($content->getObjets() as $relatedContentString) {
                $relatedContent = explode(':', $relatedContentString);
                $relatedContentType = $relatedContent[0];
                $relatedContentId = $relatedContent[1];

                if ($relatedContentType == 'INFRADOC') {
                    $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                        $this->contentManager->findOneById(
                            $relatedContentId
                        )
                    );
                }
            }
        }

        return $relatedResources;
    }
}
