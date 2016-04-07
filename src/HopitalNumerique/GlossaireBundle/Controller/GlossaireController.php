<?php

namespace HopitalNumerique\GlossaireBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Glossaire controller.
 */
class GlossaireController extends Controller
{
    /**
     * Affiche la liste des Glossaire.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_glossaire.grid.glossaire');

        return $grid->render('HopitalNumeriqueGlossaireBundle:Glossaire:index.html.twig');
    }

    /**
     * Suppresion d'un Glossaire.
     * 
     * @param integer $id Id de Glossaire.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $glossaire = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_glossaire.manager.glossaire')->delete( $glossaire );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_glossaire_glossaire').'"}', 200);
    }

    /**
     * Parse les publication à la recherche de mots du glossaire
     *
     * @return redirect
     */
    public function parsePublicationsAction()
    {
        $objets   = $this->get('hopitalnumerique_objet.manager.objet')->findAll();
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->findAll();
        $this->get('hopitalnumerique_glossaire.manager.glossaire')->parsePublications( $objets, $contenus );

        //save changes
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('info', 'Publications parsées avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_glossaire_glossaire') );
    }
}