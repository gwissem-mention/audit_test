<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back\Objet;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CoreBundle\Domain\Command\Relation\LinkObjectCommand;
use HopitalNumerique\CoreBundle\Domain\Command\Relation\LinkObjectHandler;
use HopitalNumerique\ObjetBundle\Domain\Command\ReorderRelatedBoardsCommand;

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
        $currentObject = $this->get('hopitalnumerique_objet.repository.objet')->find($this->get('request')->request->get('objectId'));
        $selectedBoards = $this->get('hopitalnumerique_forum.repository.board')->findById($this->get('request')->request->get('boards'));

        foreach ($selectedBoards as $board) {
            $this->get(LinkObjectHandler::class)->handle(new LinkObjectCommand($currentObject, $board));
        }

        $this->addFlash('success', $this->get('translator')->trans('related_board.link.add.success'));

        return new JsonResponse([
            'url' => $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $currentObject->getId()]),
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

        return new JsonResponse();
    }
}
