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
        $user          = $this->get('security.context')->getToken()->getUser();
        $role          = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, $role, 2 );

        return $this->render('HopitalNumeriqueCoreBundle:Default:index.html.twig', array(
            'article'    => $article,
            'actualites' => $actualites
        ));
    }
}