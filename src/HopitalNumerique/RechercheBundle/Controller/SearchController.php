<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction( $id = null )
    {
        $elements = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        //on prépare la session
        $session = $this->getRequest()->getSession();

        //on essaye de charger la requete par défaut
        if ( is_null($id) ){
            //si on a quelque chose en session, on charge la session
            if( !is_null($session->get('requete-refs')) ){
                $requete = null;
                $refs    = $session->get('requete-refs');
            //sinon on charge la requete par défaut
            }else{
                $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'isDefault' => true ) );
                $refs    = $requete ? json_encode($requete->getRefs()) : '[]';
            }
        //on charge la requete demandée explicitement
        }else{
            $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'id' => $id ) );
            $refs    = $requete ? json_encode($requete->getRefs()) : '[]';
        }

        if( $refs == 'null' )
            $refs = '[]';

        $session->set('requete-refs', $refs );

        return $this->render('HopitalNumeriqueRechercheBundle:Search:index.html.twig', array(
            'elements' => $elements['CATEGORIES_RECHERCHE'],
            'requete'  => $requete,
            'refs'     => $refs
        ));
    }

    /**
     * Retourne les résultats de la recherche
     */
    public function getResultsAction()
    {
        //On récupère le role de l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);

        $references = $this->get('request')->request->get('references');
        $objets     = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $role );
        
        //on prépare la session
        $session = $this->getRequest()->getSession();
        $session->set('requete-refs', json_encode($references) );

        //get Cookies Stuff
        $request = $this->get('request');
        $cookies = $request->cookies;

        //set Cookies vals
        $showMorePointsDurs  = $cookies->has('showMorePointsDurs') ? $cookies->get('showMorePointsDurs') : 2;
        $showMoreProductions = $cookies->has('showMoreProductions') ? $cookies->get('showMoreProductions') : 2;

        return $this->render('HopitalNumeriqueRechercheBundle:Search:getResults.html.twig', array(
            'objets'              => $objets,
            'showMorePointsDurs'  => $showMorePointsDurs,
            'showMoreProductions' => $showMoreProductions
        ));
    }
}