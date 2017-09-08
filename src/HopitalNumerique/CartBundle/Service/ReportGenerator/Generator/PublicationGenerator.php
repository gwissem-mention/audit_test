<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\CartBundle\Model\Report\Publication;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class PublicationGenerator implements ItemGeneratorInterface
{
    /**
     * @var ContenuManager $contentManager
     */
    protected $contentManager;

    /**
     * @var Referencement $referencement
     */
    protected $referencement;

    /**
     * PublicationGenerator constructor.
     *
     * @param ContenuManager $contentManager
     * @param Referencement $referencement
     */
    public function __construct(
        ContenuManager $contentManager,
        Referencement $referencement
    ) {
        $this->contentManager = $contentManager;
        $this->referencement = $referencement;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function support($object)
    {
        return $object instanceof Objet;
    }

    /**
     * @param Objet $publication
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($publication, Report $report)
    {
        $referencesTree = $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
            $publication->getDomaines(),
            Entity::ENTITY_TYPE_OBJET,
            $publication->getId()
        );

        $item = new Publication(
            $publication,
            $this->contentManager->getArboForObjet($publication->getId()),
            $referencesTree
        );

        return $item;
    }

}
