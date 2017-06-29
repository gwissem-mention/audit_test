<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\CDPGroup;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;

class CDPGroupGenerator implements ItemGeneratorInterface
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
        return $object instanceof Groupe;
    }

    /**
     * @param Groupe $group
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($group, Report $report)
    {
        $item = new CDPGroup(
            $group,
            $this->entityHasReferenceRepository->findByTypeAndId(Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE, $group->getId())
        );

        if ($group->getUsers()->contains($report->getOwner())) {
            $item->setSummary($group->getFiches());
        }


        return $item;
    }

}
