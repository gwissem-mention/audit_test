<?php
namespace HopitalNumerique\UserBundle\Controller;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller des Résultats par utilisateurs
 */
class ResultatController extends Controller
{
    public function indexAction( User $user )
    {
        $resultats = $this->get('hopitalnumerique_autodiag.manager.resultat')->findBy( array('user'=>$user) );

        return $this->render( 'HopitalNumeriqueUserBundle:Resultat:index.html.twig' , array(
            'user'      => $user,
            'options'   => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'resultats' => $resultats
        ));
    }
}