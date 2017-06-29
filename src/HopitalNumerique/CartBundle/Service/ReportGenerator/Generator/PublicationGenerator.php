<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\CartBundle\Model\Report\Publication;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class PublicationGenerator implements ItemGeneratorInterface
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
        $item = new Publication(
            $publication,
            $this->entityHasReferenceRepository->findByTypeAndId(Entity::ENTITY_TYPE_OBJET, $publication->getId())
        );

        return $item;
    }

}
