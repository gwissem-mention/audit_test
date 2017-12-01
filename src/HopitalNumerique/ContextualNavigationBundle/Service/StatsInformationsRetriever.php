<?php

namespace HopitalNumerique\ContextualNavigationBundle\Service;

use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\ForumBundle\Repository\TopicRepository;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;

/**
 * Class StatsInformationsRetriever
 */
class StatsInformationsRetriever
{
    /**
     * @var ObjetRepository
     */
    protected $objectRepository;

    /**
     * @var TopicRepository
     */
    protected $topicRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * LostInformationRetriever constructor.
     *
     * @param ObjetRepository $objetRepository
     * @param TopicRepository $topicRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        ObjetRepository $objetRepository,
        TopicRepository $topicRepository,
        UserRepository $userRepository
    ) {
        $this->objectRepository = $objetRepository;
        $this->topicRepository = $topicRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return [
            'methods_tools' => $this->objectRepository->getProductionsCount(),
            'users' => $this->userRepository->countAllUsers(),
            'forum_topics' => $this->topicRepository->countAllTopics(),
            'cdp_members' => $this->userRepository->countAddCDPUsers(),
        ];
    }

}
