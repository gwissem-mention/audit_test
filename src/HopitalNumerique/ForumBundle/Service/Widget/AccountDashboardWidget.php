<?php

namespace HopitalNumerique\ForumBundle\Service\Widget;

use HopitalNumerique\ForumBundle\Repository\TopicRepository;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

class AccountDashboardWidget extends WidgetAbstract
{
    /**
     * @var TopicRepository $topicRepository
     */
    protected $topicRepository;

    /**
     * @param TopicRepository $topicRepository
     */
    public function setTopicRepository(TopicRepository $topicRepository)
    {
        $this->topicRepository = $topicRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $topics = $this->topicRepository->getLastTopicWhereUserIsInvolved($this->tokenStorage->getToken()->getUser());

        $html = $this->twig->render('@HopitalNumeriqueForum/Widget/dashboardWidget.html.twig', [
            'topics' => $topics,
        ]);

        $title = $this->translator->trans('title', [], 'forumWidget');

        return new Widget('forum', $title, $html);
    }
}
