<?php

namespace HopitalNumerique\RegistreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AmbassadeurController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        //get connected user and Ambaassadeurs
        $user         = $this->get('security.context')->getToken()->getUser();
        $region       = $user->getRegion();
        $ambassadeurs = $region ? $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegion( $region ) : array();

        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:index.html.twig', array(
            'ambassadeurs' => $ambassadeurs,
            'region'       => $region ? $region : null
        ));
    }

    /**
     * Affiche la liste des objets maitrisés par l'ambasssadeur dans une popin
     *
     * @param integer $id ID de l'user
     */
    public function objetsAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByAmbassadeur($id);
    
        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:objets.html.twig', array(
            'objets' => $objets
        ));
    }
}