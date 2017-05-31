<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back\Objet;

use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\RelatedBoard;
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
        $currentObjectId = $this->get('request')->request->get('objectId');
        $selectedBoardsId = $this->get('request')->request->get('boards');

        /** @var Objet $object */
        $currentObject = $this->get('hopitalnumerique_objet.repository.objet')->findOneBy(['id' => $currentObjectId]);
        $relatedBoards = $currentObject->getRelatedBoards();
        $boardsId = [];

        /** @var RelatedBoard $relatedBoard */
        foreach ($relatedBoards as $relatedBoard) {
            $boardsId[] = $relatedBoard->getBoard()->getId();
        }

        foreach ($selectedBoardsId as $selectedBoardId) {
            if (!in_array($selectedBoardId, $boardsId)) {
                $board = $this->get('ccdn_forum_forum.repository.board')->findOneBoardById($selectedBoardId);
                $currentObject->addRelatedBoard(new RelatedBoard($currentObject, $board));
            }
        }

        $this->get('hopitalnumerique_objet.manager.objet')->save($currentObject);

        $this->get('session')->getFlashBag()->add('success', 'Les boards ont été liés à la publication.');

        return new Response(
            '{"success":true, "url" : "'
            . $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $currentObjectId])
            . '"}',
            200
        );
    }

    /**
     * @param Objet $object
     * @param Board $board
     *
     * @return Response
     */
    public function deleteLinkAction(Objet $object, Board $board)
    {
        /** @var RelatedBoard $relatedBoard */
        $relatedBoard = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(RelatedBoard::class)
            ->findOneBy(['object' => $object->getId(), 'board' => $board->getId()])
        ;

        $object->removeRelatedBoard($relatedBoard);

        $this->get('doctrine.orm.default_entity_manager')->flush($object);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl(
                'hopitalnumerique_objet_objet_edit',
                ['id' => $object->getId()]
            ) . '"}',
            200
        );
    }

    /**
     * @param Objet $object
     *
     * @return Response
     */
    public function reorderAction(Objet $object)
    {
        $relatedBoards = $this
            ->get('doctrine')
            ->getRepository(RelatedBoard::class)
            ->findBy(['object' => $object->getId()])
        ;

        $relatedBoardsIndexedByBoardId = [];

        /** @var RelatedBoard $relatedBoard */
        foreach ($relatedBoards as $relatedBoard) {
            $relatedBoardsIndexedByBoardId[$relatedBoard->getBoard()->getId()] = $relatedBoard;
        }

        $boards = $this->get('request')->request->get('boards');

        $i = 1;

        foreach ($boards as $board) {
            if ($relatedBoardsIndexedByBoardId[$board['id']]->getPosition() != $i) {
                $relatedBoardsIndexedByBoardId[$board['id']]->setPosition($i);
            }

            $i++;
        }

        $this->get('doctrine.orm.default_entity_manager')->flush($relatedBoardsIndexedByBoardId);

        return new Response('{"success":true}', 200);
    }
}
