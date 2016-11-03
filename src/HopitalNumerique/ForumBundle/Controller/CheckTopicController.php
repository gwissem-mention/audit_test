<?php

namespace HopitalNumerique\ForumBundle\Controller;

use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CheckTopicController extends Controller
{


    public function CheckAction($objet)
    {
        $topic = $this->container->get('hopitalnumerique_forum.manager.topic')->findOneBy(array(
            'title' => $objet
        ));
        $board = $this->container->get('hopitalnumerique_forum.manager.board')->findOneBy(array(
            'id' => $this->getParameter('ref_board_create_topic')
        ));
        if ($topic == null) {

            $topic = new Topic();
            $post = new Post();

            $post->setCreatedBy($this->getUser());
            $post->setBody('Venez discuter de ce sujet: '.$objet);
            $post->setCreatedDate(\DateTime::createFromFormat('d-m-Y H:i:s', date('d-m-Y H:i:s')));
            $topic->setBoard($board);
            $topic->setTitle($objet);
            $topic->setFirstPost($post);
            $topic->setLastPost($post);
            $post->setTopic($topic);
            $em = $this->getDoctrine()->getManager();

            $em->persist($topic);
            $em->flush();

            return $this->redirectToRoute('ccdn_forum_user_topic_show', array('topicId' => $topic->getId()));

        } else {
            return $this->redirectToRoute('ccdn_forum_user_topic_show', array('topicId' => $topic->getId()));
        }
    }

}
