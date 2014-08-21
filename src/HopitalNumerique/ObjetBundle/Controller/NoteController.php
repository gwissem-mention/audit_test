<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     */
    public function addAction(Request $request)
    {
        $infraDoc = null;

        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //récupération de l'objet du commentaire passé en param de la requete
        $isContenu = $request->request->get('isContenu') === "1";

        //Si c'est un Infradoc
        if( $isContenu )
        {
            $idInfraDoc = $request->request->get('objetId');
            $infraDoc   = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $idInfraDoc) );
            $objet      = $infraDoc->getObjet();
            $note       = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(array('contenu' => $infraDoc, 'user' => $user));
        }
        //Ou un objet
        else
        {
            $idObjet = $request->request->get('objetId');
            $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $idObjet));
            $note    = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(array('objet' => $objet, 'user' => $user));
        }

        //Récupération de la note
        $noteValeur = intval($request->request->get('note'));
        //Si null, on le créé
        if(is_null($note))
        {
            $note = $this->get('hopitalnumerique_objet.manager.note')->createEmpty();

            $note->setUser($user);
            $note->setObjet($objet);
            if(!is_null($infraDoc))
                $note->setContenu($infraDoc);
        }
        
        //Set de la nouvelle valeur de la note + modif de la date
        $note->setNote($noteValeur);
        $note->setDateNote(new \DateTime());

        //Sauvegarde / Insertion
        $this->get('hopitalnumerique_objet.manager.note')->save($note);

        return new Response('{"success":true}', 200);
    }

    /**
     * Calcul de la note moyenne en AJAX d'un objet.
     *
     * @param Request $request
     */
    public function calculNoteMoyenneAction(Request $request)
    {
        //récupération de l'objet du commentaire passé en param de la requete
        $isContenu = $request->request->get('isContenu') === "1";

        $idObjet     = $request->request->get('objetId');

        //Si c'est un Infradoc
        if( $isContenu )
        {
            $objet = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $idObjet));
        }
        else
        {
            $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $idObjet));
        }
        $noteMoyenne = $this->get('hopitalnumerique_objet.manager.note')->getMoyenneNoteByObjet($objet->getId(), $isContenu);

        $nombreNotes = $this->get('hopitalnumerique_objet.manager.note')->countNbNoteByObjet($objet->getId(), $isContenu);

        return new Response('{"success":true, "nbNote" : "'.$nombreNotes.'", "noteMoyenne" : "'. $noteMoyenne .'"}', 200);
    }

    /**
     * Suppression de la note de l'utilisateur courant pour un objet donné (click sur reset note).
     *
     * @param Request $request
     */
    public function deleteNoteAction(Request $request)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //récupération de l'objet du commentaire passé en param de la requete
        $isContenu = $request->request->get('isContenu') === "1";

        //Si c'est un Infradoc
        if( $isContenu )
        {
            $idInfraDoc = $request->request->get('objetId');
            $infraDoc   = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $idInfraDoc) );
            $objet      = $infraDoc->getObjet();
            $note       = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(array('contenu' => $infraDoc, 'user' => $user));
        }
        //Ou un objet
        else
        {
            $idObjet = $request->request->get('objetId');
            $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $idObjet));
            $note    = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(array('objet' => $objet, 'user' => $user));
        }
        
        if(!is_null($note))
            $this->get('hopitalnumerique_objet.manager.note')->delete($note);

        return new Response('{"success":true}', 200);
    }
}
