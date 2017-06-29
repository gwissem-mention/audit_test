<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\CartBundle\Model\Report\ForumTopic;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;

class ForumTopicGenerator implements ItemGeneratorInterface
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
        return $object instanceof Topic;
    }

    /**
     * @param ForumTopic $object
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($forumTopic, Report $report)
    {
        $item = new ForumTopic(
            $forumTopic,
            $this->entityHasReferenceRepository->findByTypeAndId(Entity::ENTITY_TYPE_FORUM_TOPIC, $forumTopic->getId())
        );

        return $item;
    }

}
