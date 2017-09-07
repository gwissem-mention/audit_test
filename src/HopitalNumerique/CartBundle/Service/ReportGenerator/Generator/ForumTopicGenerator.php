<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\CartBundle\Model\Report\ForumTopic;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class ForumTopicGenerator implements ItemGeneratorInterface
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
        return $object instanceof Topic;
    }

    /**
     * @param Topic $forumTopic
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($forumTopic, Report $report)
    {
        $item = new ForumTopic(
            $forumTopic,
            $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
                $forumTopic->getBoard()->getCategory()->getDomaines(),
                Entity::ENTITY_TYPE_FORUM_TOPIC,
                $forumTopic->getId()
            )
        );

        return $item;
    }

}
