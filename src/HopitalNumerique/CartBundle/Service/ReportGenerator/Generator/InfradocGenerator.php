<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\CartBundle\Model\Report\Infradoc;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class InfradocGenerator implements ItemGeneratorInterface
{
    /**
     * @var Referencement $referencement
     */
    protected $referencement;

    /**
     * PublicationGenerator constructor.
     *
     * @param Referencement $referencement
     */
    public function __construct(Referencement $referencement)
    {
        $this->referencement = $referencement;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function support($object)
    {
        return $object instanceof Contenu;
    }

    /**
     * @param Contenu $content
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($content, Report $report)
    {
        $item = new Infradoc(
            $content,
            $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
                $content->getObjet()->getDomaines(),
                Entity::ENTITY_TYPE_CONTENU,
                $content->getId()
            )
        );

        return $item;
    }

}
