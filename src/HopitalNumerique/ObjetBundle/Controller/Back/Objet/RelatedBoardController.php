<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back\Objet;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RelatedBoardController
 */
class RelatedBoardController extends Controller
{
    /**
     * @param Objet $object
     *
     * @return Response
     */
    public function addLinkAction(Objet $object)
    {
        $boards = $this->get('ccdn_forum_forum.repository.board')->findAllBoards();

        return $this->render('HopitalNumeriqueObjetBundle:Objet:add_board.html.twig', [
            'boards' => $boards,
            'object' => $object,
        ]);
    }

    /**
     * @return Response
     */
    public function saveLinkAction()
    {
        $objectId = $this->get('request')->request->get('objectId');
        $boards = $this->get('request')->request->get('boards');

        /** @var Objet $object */
        $object = $this->get('hopitalnumerique_objet.repository.objet')->findOneBy(['id' => $objectId]);
        $relatedBoards = $object->getRelatedBoards();

        foreach ($boards as $board) {
            if (!$relatedBoards->contains($board)) {
                $object->addRelatedBoard($board);
            }
        }

        $this->get('hopitalnumerique_objet.manager.objet')->save($object);

        $this->get('session')->getFlashBag()->add('success', 'Les boards ont été liés à la publication.');

        return new Response(
            '{"success":true, "url" : "'
            . $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $objectId])
            . '"}',
            200
        );
    }

    public function deleteLinkAction()
    {

    }

    public function reorderAction()
    {

    }
}
