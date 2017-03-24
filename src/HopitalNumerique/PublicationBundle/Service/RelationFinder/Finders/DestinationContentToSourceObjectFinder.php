<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

use HopitalNumerique\ObjetBundle\DependencyInjection\ProductionLiee;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\PublicationBundle\Service\RelationFinder\RelationFinder;

/**
 * Class DestinationContentToSourceObjectFinder
 *
 * Retrieves objects related with current content (B1->A)
 */
class DestinationContentToSourceObjectFinder implements FinderInterface
{
    /**
     * @var ObjetRepository
     */
    protected $objectRepository;

    /**
     * @var ContenuManager
     */
    protected $contentManager;

    /**
     * @var ProductionLiee
     */
    protected $relatedResourceTransformer;

    /**
     * @var RelationFinder
     */
    protected $relationFinder;

    /**
     * DestinationContentToSourceObjectFinder constructor.
     *
     * @param ObjetRepository $objetRepository
     * @param ContenuManager  $contenuManager
     * @param ProductionLiee  $productionLiee
     * @param RelationFinder  $relationFinder
     */
    public function __construct(
        ObjetRepository $objetRepository,
        ContenuManager $contenuManager,
        ProductionLiee $productionLiee,
        RelationFinder $relationFinder
    ) {
        $this->objectRepository = $objetRepository;
        $this->contentManager = $contenuManager;
        $this->relatedResourceTransformer = $productionLiee;
        $this->relationFinder = $relationFinder;
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

        $objects = $this->objectRepository->findAll();

        /** @var Objet $object */
        foreach ($objects as $object) {
            if (!is_null($object->getObjets())) {
                foreach ($object->getObjets() as $relatedContentString) {
                    $relatedContent = explode(':', $relatedContentString);
                    $relatedContentType = $relatedContent[0];
                    $relatedContentId = $relatedContent[1];

                    if ($relatedContentType == 'INFRADOC' && $relatedContentId == $content->getId()) {
                        $relatedResources[] = $this->relatedResourceTransformer->formatProductionsLiees(
                            $this->objectRepository->findOneBy(['id' => $object->getId()])
                        );
                    }
                }
            }
        }

        $contentParent = $content->getParent();

        while (!is_null($contentParent)) {
            $relatedResources = array_merge(
                $relatedResources,
                $this->relationFinder->findRelations(
                    $contentParent
                )
            );

            $contentParent = $contentParent->getParent();
        }

        $relatedResources = array_merge(
            $relatedResources,
            $this->relationFinder->findRelations($content->getObjet())
        );

        return $relatedResources;
    }
}
