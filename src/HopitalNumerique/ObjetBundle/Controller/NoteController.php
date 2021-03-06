<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use HopitalNumerique\ObjetBundle\Form\NoteCommentaireType;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Note controller.
 */
class NoteController extends Controller
{
    /**
     * Ajout d'une note en AJAX ou modification d'une existante.
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function addAction(Request $request)
    {
        $entityId = $request->request->getInt('objetId');
        $noteValeur = $request->request->getInt('note');
        $commentValue = $request->request->get('comment');

        if ('1' === $request->request->get('isContenu')) {
            $this->container->get('hopitalnumerique_objet.doctrine.note_saver')->saveNoteForContenu(
                $noteValeur,
                $this->container->get('hopitalnumerique_objet.manager.contenu')->findOneById($entityId),
                $this->getUser(),
                $commentValue,
                $request->getClientIp()
            );
        } else { // Objet
            $this->container->get('hopitalnumerique_objet.doctrine.note_saver')->saveNoteForObjet(
                $noteValeur,
                $this->container->get('hopitalnumerique_objet.manager.objet')->findOneById($entityId),
                $this->getUser(),
                $commentValue,
                $request->getClientIp()
            );
        }

        return new Response('{"success":true}', 200);
    }

    /**
     * Calcul de la note moyenne en AJAX d'un objet.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function calculNoteMoyenneAction(Request $request)
    {
        $nombreNotes = 0;
        $noteMoyenne = 0;
        $reviewCountByMark = [];

        //récupération de l'objet du commentaire passé en param de la requete
        $isContenu = $request->request->get('isContenu') === '1';

        $idObjet = $request->request->get('objetId');

        //Si c'est un Infradoc
        if ($isContenu) {
            $objet = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(['id' => $idObjet]);
        } else {
            $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $idObjet]);
        }
        if (!is_null($objet)) {
            $noteMoyenne = $this->get('hopitalnumerique_objet.manager.note')->getMoyenneNoteByObjet($objet->getId(), $isContenu);

            $nombreNotes = $this->get('hopitalnumerique_objet.manager.note')->countNbNoteByObjet($objet->getId(), $isContenu);

            $reviewCountByMark = $this->get('hopitalnumerique_objet.manager.note')->countReviewByMark($objet->getId(), $isContenu);
        }

        return new JsonResponse([
            'success' => true,
            'nbNote' => $nombreNotes,
            'noteMoyenne' => $noteMoyenne,
            'userCanVote' => $this->container->get('hopitalnumerique_objet.doctrine.note_reader')->userCanVote($objet, $this->getUser()),
            'reviewByMark' => $reviewCountByMark,
        ]);
    }

    /**
     * Suppression de la note de l'utilisateur courant pour un objet donné (click sur reset note).
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteNoteAction(Request $request)
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            //Si c'est un Infradoc
            if ($request->request->get('isContenu') === '1') {
                $idInfraDoc = $request->request->get('objetId');
                $infraDoc = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(['id' => $idInfraDoc]);
                $objet = $infraDoc->getObjet();
                $note = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(['contenu' => $infraDoc, 'user' => $user]);
            }
            //Ou un objet
            else {
                $idObjet = $request->request->get('objetId');
                $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $idObjet]);
                $note = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(['objet' => $objet, 'user' => $user]);
            }

            if (!is_null($note)) {
                $this->get('hopitalnumerique_objet.manager.note')->delete($note);
            }
        }

        return new Response('{"success":true}', 200);
    }
}
