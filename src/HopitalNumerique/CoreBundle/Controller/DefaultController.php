<?php

namespace HopitalNumerique\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $article = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => 1) );


        return $this->render('HopitalNumeriqueCoreBundle:Default:index.html.twig', array(
            'article' => $article
        ));
    }
}