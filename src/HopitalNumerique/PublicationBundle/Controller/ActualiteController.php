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
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );
        $user          = $this->get('security.context')->getToken()->getUser();
        $role          = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, $role );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', array(
            'actualites' => $actualites,
            'type'       => 'normal'
        ));
    }

    /**
     * Article ambassadeur
     */
    public function ambassadeurAction()
    {
        //on récupère les actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 570) );
        $user          = $this->get('security.context')->getToken()->getUser();
        $role          = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, $role );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', array(
            'actualites' => $actualites,
            'type'       => 'ambassadeur'
        ));
    }

    /**
     * Article Action
     */
    public function categorieAction($id, $libelle)
    {
        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'id' => $id) );
        $user       = $this->get('security.context')->getToken()->getUser();
        $role       = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $categories, $role );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', array(
            'actualites' => $actualites
        ));
    }

    /**
     * Partial render : bloc liste des actualités colonne left
     */
    public function actualitesAction()
    {
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );

        //Show categ with articles only
        $categories = $this->get('hopitalnumerique_objet.manager.objet')->getCategoriesWithArticles( $allCategories );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:actualites.html.twig', array(
            'categories' => $categories
        ));
    }
}
