<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

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
        //$session = new Session();
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
            $refs    = json_encode($requete->getRefs());
        }

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
        $role = $this->get('nodevo_role.manager.role')->getConnectedUserRole();

        $references = $this->get('request')->request->get('references');
        $objets     = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $role );
        
        //on prépare la session
        $session = new Session();
        $session->set('requete-refs', json_encode($references) );
        
        return $this->render('HopitalNumeriqueRechercheBundle:Search:getResults.html.twig', array(
            'objets' => $objets
        ));
    }

    /**
     * Affiche la synthèse de l'objet dans une grande popin
     */
    public function syntheseAction($id)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //test si l'user connecté à le rôle requis pour voir la synthèse
        $role   = $this->get('nodevo_role.manager.role')->getConnectedUserRole();
        $params = array();
        if( $this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) )
            $params['objet'] = $objet;
        
        return $this->render('HopitalNumeriqueRechercheBundle:Search:synthese.html.twig', $params);
    }
}