<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back\Objet;

use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ObjetBundle\Domain\Command\LinkBoardToObjectCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\ReorderRelatedBoardsCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\UnlinkBoardToObjectCommand;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $currentObjectId = $this->get('request')->request->get('objectId');
        $selectedBoardsId = $this->get('request')->request->get('boards');

        $linkBoardToObjectCommand = new LinkBoardToObjectCommand($currentObjectId, $selectedBoardsId);

        $this->get('hopitalnumerique_objet.link_board_to_object_handler')->handle($linkBoardToObjectCommand);

        $this->get('session')->getFlashBag()->add('success', 'Les boards ont été liés à la publication.');

        return new JsonResponse([
            'success' => true,
            'url' => $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $currentObjectId]),
        ]);
    }

    /**
     * @param Objet $object
     * @param Board $board
     *
     * @return Response
     */
    public function deleteLinkAction(Objet $object, Board $board)
    {
        $unlinkBoardToObjectCommand = new UnlinkBoardToObjectCommand($object, $board);

        $this->get('hopitalnumerique_objet.unlink_board_to_object_handler')->handle($unlinkBoardToObjectCommand);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new JsonResponse([
            'success' => true,
            'url'     => $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $object->getId()]),
        ]);
    }

    /**
     * @param Objet $object
     *
     * @return Response
     */
    public function reorderAction(Objet $object)
    {
        $boards = $this->get('request')->request->get('boards');

        $reorderRelatedBoardsCommand = new ReorderRelatedBoardsCommand($object, $boards);

        $this->get('hopitalnumerique_objet.reorder_related_board_handler')->handle($reorderRelatedBoardsCommand);

        return new JsonResponse(['success' => true]);
    }
}
