<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        $elements = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);

        return $this->render('HopitalNumeriqueRechercheBundle:Search:index.html.twig', array(
            'elements' => $elements['CATEGORIES_RECHERCHE']
        ));
    }

    /**
     * Retourne les résultats de la recherche
     */
    public function getResultsAction()
    {
        //On récupère l'user connecté et son role
        $user = $this->get('security.context')->getToken()->getUser();

        $references = $this->get('request')->request->get('references');
        $objets     = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $user );
        
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
        $user  = $this->get('security.context')->getToken()->getUser();
        if( $user === 'anon.')
            $role = 'ROLE_ANONYME_10';
        else{
            //on récupère le rôle de l'user connecté
            $roles = $user->getRoles();
            $role  = $roles[0];
        }

        $params = array();
        if( $this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) )
            $params['objet'] = $objet;
        
        return $this->render('HopitalNumeriqueRechercheBundle:Search:synthese.html.twig', $params);
    }
}