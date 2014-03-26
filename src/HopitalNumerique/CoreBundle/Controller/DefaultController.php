<?php

namespace HopitalNumerique\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $article = $this->get('hopitalnumerique_objet.manager.objet')->getArticleHome();

        //get actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, 2 );

        return $this->render('HopitalNumeriqueCoreBundle:Default:index.html.twig', array(
            'article'    => $article,
            'actualites' => $actualites
        ));
    }
}