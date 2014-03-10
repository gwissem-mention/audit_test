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

        //test si l'user connecté à le rôle requis pour voir la synthèse
        $role = $this->get('nodevo_role.manager.role')->getConnectedUserRole();
        if( !$this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas accès à cette publication.' );
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }

        //Types objet
        $type  = array();
        $types = $objet->getTypes();
        foreach ($types as $one)
            $type[] = $one->getLibelle();

        //get Contenus : for sommaire
        $contenus = $objet->getIsInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //render
        return $this->render('HopitalNumeriqueRechercheBundle:Publication:objet.html.twig', array(
            'objet'    => $objet,
            'types'    => implode(' ♦ ', $type),
            'contenus' => $contenus
        ));
    }

    /**
     * Contenu Action
     */
    public function contenuAction($id, $alias, $idc, $aliasc)
    {
        $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $idc ) );

        //Types objet
        $type  = array();
        $types = $objet->getTypes();
        foreach ($types as $one)
            $type[] = $one->getLibelle();

        //get Contenus : for sommaire
        $contenus = $objet->getIsInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //render
        return $this->render('HopitalNumeriqueRechercheBundle:Publication:objet.html.twig', array(
            'objet'    => $objet,
            'contenus' => $contenus,
            'types'    => implode(' ♦ ', $type),
            'contenu'  => $contenu
        ));
    }
}