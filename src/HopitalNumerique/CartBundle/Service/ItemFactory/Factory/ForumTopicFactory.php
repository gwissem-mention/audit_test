<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\CartBundle\Model\Item\ForumTopic;
use HopitalNumerique\ForumBundle\Repository\TopicRepository;

class ForumTopicFactory extends Factory
{
    /**
     * @var TopicRepository $topicRepository
     */
    protected $topicRepository;

    /**
     * ObjectFactory constructor.
     *
     * @param TopicRepository $topicRepository
     */
    public function __construct(TopicRepository $topicRepository)
    {
        $this->topicRepository = $topicRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::FORUM_TOPIC_TYPE;
    }

    /**
     * @param $content
     *
     * @return ForumTopic
     */
    public function build($content)
    {
        return new ForumTopic($content);
    }

    /**
     * @param array $itemIds
     *
     * @return Topic[]
     */
    public function getMultiple($itemIds)
    {
        return $this->topicRepository->findByIdsWithJoin($itemIds);
    }

    /**
     * @param $itemId
     *
     * @return null|Topic
     */
    public function get($itemId)
    {
        return $this->topicRepository->findByIdWithJoin($itemId);
    }
}
