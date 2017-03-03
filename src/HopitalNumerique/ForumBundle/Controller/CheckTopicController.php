<?php

namespace HopitalNumerique\ForumBundle\Controller;

use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ForumBundle\Entity\Forum;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CheckTopicController extends Controller
{
    /**
     * @param Objet $objet
     *
     * @return RedirectResponse
     */
    public function checkAction(Objet $objet)
    {
        /** @var Topic $topic */
        $topic = $this->get('hopitalnumerique_forum.manager.topic')->findOneBy([
            'title' => $objet->getTitre(),
        ]);

        /** @var Board $board */
        $board = $this->get('hopitalnumerique_forum.manager.board')->findOneBy([
            'id' => $this->getParameter('ref_board_create_topic'),
        ]);

        /** @var Forum $forum */
        $forum = $this->get('hopitalnumerique_forum.manager.forum')->findOneBy([
            'id' => $this->getParameter('ref_forum_create_topic'),
        ]);

        if ($topic == null) {
            return $this->redirectToRoute(
                'hopital_numerique_forum_user_topic_create',
                [
                    'forum' => $forum->getId(),
                    'board' => $board->getId(),
                    'objet' => $objet->getId(),
                ]
            );
        } else {
            return $this->redirectToRoute('ccdn_forum_user_topic_show', ['topicId' => $topic->getId()]);
        }
    }
}
