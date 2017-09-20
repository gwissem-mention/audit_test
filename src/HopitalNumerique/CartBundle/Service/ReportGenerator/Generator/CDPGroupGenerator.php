<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\CDPGroup;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;

class CDPGroupGenerator implements ItemGeneratorInterface
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
            $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
                $group->getDomains(),
                Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE,
                $group->getId()
            )
        );

        if ($group->getUsers()->contains($report->getOwner())) {
            $item->setSummary($group->getFiches());
        }


        return $item;
    }

}
