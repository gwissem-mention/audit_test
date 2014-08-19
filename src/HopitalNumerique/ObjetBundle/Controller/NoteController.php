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
     * Ajout d'un commentaire en AJAX.
     *
     * @param integer $id Id de Commentaire.
     */
    public function addAction(Request $request)
    {
        // $commentaire = $this->get('hopitalnumerique_objet.manager.commentaire')->createEmpty();

        // //récupération de l'objet du commentaire passé en param de la requete
        // $isContenu = $request->request->get('isContenu') === "1";
        // //Si c'est un Infradoc
        // if( $isContenu )
        // {
        //     $idInfraDoc = $request->request->get('objetId');
        //     $infraDoc = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $idInfraDoc) );
        //     $objet    = $infraDoc->getObjet();
        //     $commentaire->setContenu($infraDoc);
        // }
        // //Ou un objet
        // else
        // {
        //     $idObjet = $request->request->get('objetId');
        //     $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $idObjet));
        // }
        // $user    = $this->get('security.context')->getToken()->getUser();

        // $commentaire->setObjet( $objet );
        // $commentaire->setUser( $user );
        // $commentaire->setDateCreation( new \DateTime() );
        // $commentaire->setPublier(true);
        // $commentaire->setTexte($request->request->get('hopitalnumerique_commentaire')['texte']);

        // //save
        // $this->get('hopitalnumerique_objet.manager.commentaire')->save( $commentaire );

        // //return new Response('{"success":true}', 200);
        
        // return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_objet_admin_commentaire').'"}', 200); 
    }

    /**
     * Affiche le formulaire d'édition de Commentaire.
     *
     * @param integer $id Id de Commentaire.
     */
    public function editAction( $id )
    {
        // //Récupération de l'entité passée en paramètre
        // $commentaire = $this->get('hopitalnumerique_objet.manager.commentaire')->findOneBy( array('id' => $id) );

        // return $this->renderForm('hopitalnumerique_objet_commentaire', $commentaire, 'HopitalNumeriqueObjetBundle:Commentaire:edit.html.twig' );
    }
}