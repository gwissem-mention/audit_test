<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RequeteController extends Controller
{
    /**
     * Save en AJAX de la requête
     */
    public function saveAction()
    {
        $id         = $this->get('request')->request->get('id');
        $nom        = $this->get('request')->request->get('nom');
        $references = $this->get('request')->request->get('references');

        //on récupère l'objet requete ou on en crée un nouveau
        $requete = ($id == '') ? $this->get('hopitalnumerique_recherche.manager.requete')->createEmpty() : $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array('id'=>$id) );

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        $requete->setNom( $nom );
        $requete->setRefs( $references );
        $requete->setUser( $user );

        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requete );

        return new Response('{"success":true}', 200);
    }
}