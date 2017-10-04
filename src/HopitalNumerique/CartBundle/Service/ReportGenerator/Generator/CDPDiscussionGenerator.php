<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\CDPDiscussion;
use HopitalNumerique\CartBundle\Model\Report\CDPGroup;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;

class CDPDiscussionGenerator implements ItemGeneratorInterface
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
        return $object instanceof Discussion;
    }

    /**
     * @param Discussion $discussion
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($discussion, Report $report)
    {
        $item = new CDPDiscussion(
            $discussion,
            $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
                $discussion->getDomains(),
                Entity::ENTITY_TYPE_CDP_DISCUSSION,
                $discussion->getId()
            )
        );

        if ($discussion->isPublic()) {
            //$item->setSummary();
        }


        return $item;
    }

}
