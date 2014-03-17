<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicationController extends Controller
{
    /**
     * Objet Action
     */
    public function objetAction($id, $alias)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->_checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        
        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->getIsInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //render
        return $this->render('HopitalNumeriqueRechercheBundle:Publication:objet.html.twig', array(
            'objet'    => $objet,
            'types'    => $types,
            'contenus' => $contenus,
            'meta'     => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($objet->getReferences(), $objet->getResume() )
        ));
    }

    /**
     * Contenu Action
     */
    public function contenuAction($id, $alias, $idc, $aliasc)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->_checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );

        //on récupère le contenu
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $idc ) );
        $prefix  = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu);

        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->getIsInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //render
        return $this->render('HopitalNumeriqueRechercheBundle:Publication:objet.html.twig', array(
            'objet'    => $objet,
            'contenus' => $contenus,
            'types'    => $types,
            'contenu'  => $contenu,
            'prefix'   => $prefix,
            'meta'     => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($contenu->getReferences(), $contenu->getContenu() )
        ));
    }








    

    /**
     * Vérifie que l'objet est accessible à l'user connecté ET que l'objet est toujours bien publié
     *
     * @param Objet $objet L'objet
     *
     * @return boolean
     */
    private function _checkAuthorization( $objet )
    {
        $role = $this->get('nodevo_role.manager.role')->getConnectedUserRole();

        //test si l'user connecté à le rôle requis pour voir l'objet
        if( !$this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) ) {
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas accès à cette publication.' );
            return false;
        }

        $today = new \DateTime();

        //test si l'objet est publié
        if( !is_null($objet->getDateDebutPublication()) && $today < $objet->getDateDebutPublication() ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas accès à cette publication.' );
            return false;
        }

        //test si l'objet est toujours publié
        if( !is_null($objet->getDateFinPublication()) && $today > $objet->getDateFinPublication() ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas accès à cette publication.' );
            return false;
        }

        //test si l'objet est actif : état actif === 3
        if( $objet->getEtat()->getId() != 3 ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas accès à cette publication.' );
            return false;
        }

        return true;
    }
}