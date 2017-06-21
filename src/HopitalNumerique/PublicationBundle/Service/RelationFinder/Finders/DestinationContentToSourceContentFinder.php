<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;

/**
 * Class DestinationContentToSourceContentFinder
 *
 * Retrieves contents related with current content (B1->A1)
 */
class DestinationContentToSourceContentFinder implements FinderInterface
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
     * DestinationContentToSourceContentFinder constructor.
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

        $contents = $this->contentManager->findAll();

        /** @var Contenu $contentSource */
        foreach ($contents as $contentSource) {
            if (!is_null($contentSource->getObjets())) {
                foreach ($contentSource->getObjets() as $relatedContentString) {
                    $relatedContent = explode(':', $relatedContentString);
                    $relatedContentType = $relatedContent[0];
                    $relatedContentId = $relatedContent[1];

                    if ($relatedContentType == 'INFRADOC' && $relatedContentId == $content->getId()) {
                        $entity = $this->contentManager->findOneById($contentSource->getId());

                        if (null !== $entity) {
                            $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                                $entity
                            );
                        }
                    }
                }
            }
        }

        return $relatedResources;
    }
}
