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