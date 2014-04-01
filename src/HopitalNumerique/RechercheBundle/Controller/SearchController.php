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

        //si on à charger une requete, on load la bonne url
        if ( is_null($id) && !is_null($session->get('requete-id')) )
            return $this->redirect( $this->generateUrl('hopital_numerique_recherche_homepage_requete', array('id'=>$session->get('requete-id'))) );

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

                //set requete id in session
                if( $requete )
                    $session->set('requete-id', $requete->getId());
            }
        //on charge la requete demandée explicitement
        }else{
            $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'id' => $id ) );
            $refs    = $requete ? json_encode($requete->getRefs()) : '[]';

            //set requete id in session
            $session->set('requete-id', $id);
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

        $request    = $this->get('request');
        $references = $request->request->get('references');
        $objets     = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $role );
        $objets     = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $objets );

        //on prépare la session
        $session = $this->getRequest()->getSession();
        $session->set('requete-refs', json_encode($references) );

        //clean requete ID
        $cleanSession = $request->request->get('cleanSession');
        if( $cleanSession !== "false" )
            $session->set('requete-id', null);

        //get Cookies Stuff
        $cookies = $request->cookies;

        //set Cookies vals
        $showMorePointsDurs  = $cookies->has('showMorePointsDurs')  ? intval($cookies->get('showMorePointsDurs'))  : 2;
        $showMoreProductions = $cookies->has('showMoreProductions') ? intval($cookies->get('showMoreProductions')) : 2;

        return $this->render('HopitalNumeriqueRechercheBundle:Search:getResults.html.twig', array(
            'objets'              => $objets,
            'showMorePointsDurs'  => $showMorePointsDurs,
            'showMoreProductions' => $showMoreProductions
        ));
    }
}