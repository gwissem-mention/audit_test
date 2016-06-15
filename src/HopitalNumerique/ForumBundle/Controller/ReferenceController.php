<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use HopitalNumerique\ForumBundle\Entity\Topic;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    /**
     * Epingle le board
     *
     * @param  Int  idTopic
     *
     * @return Response
     */
    public function topicPinAction(Topic $topic)
    {

        $topic->setSticky(!$topic->isSticky());
        $topic->setStickiedDate(new \DateTime());
        $this->container->get('hopitalnumerique_forum.manager.topic')->save($topic);

        $response = json_encode(array('success' => $topic->isSticky() ));

        return new Response($response, 200);
    }
}