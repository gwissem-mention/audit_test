<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\Infradoc;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;

class InfradocGenerator implements ItemGeneratorInterface
{
    /**
     * @var EntityHasReferenceRepository $entityHasReferenceRepository
     */
    protected $entityHasReferenceRepository;

    /**
     * PublicationGenerator constructor.
     *
     * @param EntityHasReferenceRepository $entityHasReferenceRepository
     */
    public function __construct(EntityHasReferenceRepository $entityHasReferenceRepository)
    {
        $this->entityHasReferenceRepository = $entityHasReferenceRepository;
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
            $this->entityHasReferenceRepository->findByTypeAndId(Entity::ENTITY_TYPE_CONTENU, $content->getId())
        );

        return $item;
    }

}
