<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModuleFrontController extends Controller
{
    /**
     * Affichage de la liste des sessions d'un module, ou de tous les modules si il n'y en a pas sélectionné
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Module $module Module sélectionné pour l'affichage des sessions
     *
     * @return [type]
     */
    public function indexAction(\HopitalNumerique\ModuleBundle\Entity\Module $module = null)
    {
        //Création du tableau de modules à afficher
        $modules = array();

        //Si il n'y a pas de module spécifié on récupère l'ensemble des modules et leurs sessions
        if(is_null($module))
        {
            //Récupération de l'ensemble des modules
            $modules = $this->get('hopitalnumerique_module.manager.module')->findBy(array('statut' => 3));
        }
        //Sinon il n'y a qu'un module a afficher, celui passé en param
        else
        {
            $modules[] = $module; 
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:index.html.twig', array( 
            'modules'           => $modules,
            'moduleSelectionne' => $module
        ));

    }

    /**
     * Permet d'afficher les différents modules dans un menu
     *
     * @param HopitalNumeriqueModuleBundleEntityModule $module
     *
     * @return [type]
     */
    public function menuModulesAction(\HopitalNumerique\ModuleBundle\Entity\Module $module = null)
    {
        //Récupération de l'entité passée en paramètre
        $modules = $this->get('hopitalnumerique_module.manager.module')->findBy(array('statut' => 3));

        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:menu_modules.html.twig', array( 
            'modules'           => $modules,
            'moduleSelectionne' => $module
        ));
    }


}
