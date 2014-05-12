<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SessionFrontController extends Controller
{
    /**
     * Affiche la description d'une session dans une popin
     *
     * @param Session $session Session à afficher
     */
    public function descriptionAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:description.html.twig', array(
                'session'           => $session
        ));
    }

    /**
     * Liste toutes les informations de la session
     *
     * @param HopitalNumeriqueModuleBundleEntitySession $session [description]
     *
     * @return [type]
     */
    public function informationAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        if( ( $session->getNombrePlaceDisponible() - count($session->getInscriptions()) ) == 0 )
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('info') , 'Vous ne pouvez pas vous inscrire à cette session, il n\'y a plus de places. Veuillez-choisir une autre session ce rattachant à ce module.' );
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:index.html.twig', array(
                'session'           => $session,
                'moduleSelectionne' => $session->getModule()
        ));
    }
}
