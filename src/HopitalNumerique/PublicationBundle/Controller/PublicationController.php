<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicationController extends Controller
{
    /**
     * Objet Action
     */
    public function objetAction(Objet $objet)
    {
        //objet visualisation
        $objet->setNbVue( ($objet->getNbVue() + 1) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        
        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $objet->getId() ) : array();

        //set Consultation entry
        $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $objet );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'        => $objet,
            'objets'       => $this->getObjetsFromRecherche( $objet ),
            'types'        => $types,
            'contenus'     => $contenus,
            'meta'         => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($objet->getReferences(), $objet->getResume() ),
            'ambassadeurs' => $this->getAmbassadeursConcernes( $objet->getId() )
        ));
    }

    /**
     * Contenu Action
     */
    public function contenuAction($id, $alias, $idc, $aliasc)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );

        //on récupère le contenu
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $idc ) );
        $prefix  = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu);

        //add visualisation
        $contenu->setNbVue( ($contenu->getNbVue() + 1) );

        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //set Consultation entry
        $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $contenu, true );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'        => $objet,
            'objets'       => $this->getObjetsFromRecherche( $contenu ),
            'contenus'     => $contenus,
            'types'        => $types,
            'contenu'      => $contenu,
            'prefix'       => $prefix,
            'meta'         => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($contenu->getReferences(), $contenu->getContenu() ),
            'ambassadeurs' => $this->getAmbassadeursConcernes( $objet->getId() )
        ));
    }

    /**
     * Article Action
     */
    public function articleAction($categorie, $id, $alias)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );

        //on récupère l'item de menu courant
        $request     = $this->get('request');
        $routeName   = $request->get('_route');
        $routeParams = json_encode($request->get('_route_params'));
        $item        = $this->get('nodevo_menu.manager.item')->findOneBy( array('route'=>$routeName, 'routeParameters'=>$routeParams) );

        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );

        //get Type
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:articles.html.twig', array(
            'objet'      => $objet,
            'meta'       => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($objet->getReferences(), $objet->getResume() ),
            'menu'       => $item ? $item->getMenu()->getAlias() : null,
            'categories' => $categories,
            'types'      => $types
        ));
    }

    /**
     * Affiche la synthèse de l'objet dans une grande popin
     */
    public function syntheseAction($id)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //test si l'user connecté à le rôle requis pour voir la synthèse
        $user   = $this->get('security.context')->getToken()->getUser();
        $role   = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $params = array();
        if( $this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) )
            $params['objet'] = $objet;
        
        return $this->render('HopitalNumeriquePublicationBundle:Publication:synthese.html.twig', $params);
    }










    /**
     * Récupère les objets de la recherche et filtre sur 10 résulats maxi par catégorie
     *
     * @param Objet|Contenu $publication La publication (Objet ou Contenu)
     *
     * @return array
     */
    private function getObjetsFromRecherche( $publication )
    {
        //get Recherche results
        $session = $this->getRequest()->getSession();
        $refs    = json_decode($session->get('requete-refs'), true);

        //On récupère le role de l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);

        //on récupère sa recherche
        $objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $refs, $role );
        $objets = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $objets, $user );
        
        return $this->get('hopitalnumerique_recherche.manager.search')->formatForPublication( $objets, $publication );
    }

    /**
     * Retourne la liste des ambassadeurs concernés par la production
     *
     * @param Objet $objet La production consultée
     *
     * @return array
     */
    private function getAmbassadeursConcernes( $objet )
    {
        //get connected user and his region
        $user   = $this->get('security.context')->getToken()->getUser();
        $region = $user === 'anon.' ? false : $user->getRegion();

        return $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegionAndProduction( $region, $objet );
    }

    /**
     * Vérifie que l'objet est accessible à l'user connecté ET que l'objet est toujours bien publié
     *
     * @param Objet $objet L'objet
     *
     * @return boolean
     */
    private function checkAuthorization( $objet )
    {
        $user    = $this->get('security.context')->getToken()->getUser();
        $role    = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $message = 'Vous n\'avez pas accès à cette publication.';

        //test si l'user connecté à le rôle requis pour voir l'objet
        if( !$this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) ) {
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        $today = new \DateTime();

        //test si l'objet est publié
        if( !is_null($objet->getDateDebutPublication()) && $today < $objet->getDateDebutPublication() ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        //test si l'objet est toujours publié
        if( !is_null($objet->getDateFinPublication()) && $today > $objet->getDateFinPublication() ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        //test si l'objet est actif : état actif === 3
        if( $objet->getEtat()->getId() != 3 ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        return true;
    }
}