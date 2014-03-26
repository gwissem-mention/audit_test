<?php

namespace HopitalNumerique\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Nodevo\ToolsBundle\Tools\Chaine;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $article = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => 1) );

        /* Get catégorie for route */
        $types     = $article->getTypes();
        $type      = $types[0];
        $categorie = '';

        if($type) {
            if( $parent = $type->getParent() )
                $categorie .= $parent->getLibelle().'-';
            $categorie .= $type->getLibelle();
        }
        //clean categ
        $tool = new Chaine( $categorie );

        //get actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, 2 );

        return $this->render('HopitalNumeriqueCoreBundle:Default:index.html.twig', array(
            'article'    => $article,
            'categorie'  => $tool->minifie(),
            'actualites' => $actualites
        ));
    }
}