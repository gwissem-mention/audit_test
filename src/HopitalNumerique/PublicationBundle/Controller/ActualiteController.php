<?php
namespace HopitalNumerique\PublicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ActualiteController extends Controller
{
    /**
     * Article Action
     */
    public function indexAction()
    {
        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $categories );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', array(
            'categories' => $categories,
            'actualites' => $actualites
        ));
    }
}