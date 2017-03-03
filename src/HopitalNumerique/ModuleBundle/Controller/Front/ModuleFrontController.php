<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModuleFrontController extends Controller
{
    /**
     * Affichage de la liste des sessions d'un module, ou de tous les modules si il n'y en a pas sélectionné.
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Module $module Module sélectionné pour l'affichage des sessions
     *
     * @return [type]
     */
    public function indexAction()
    {
        //Récupération du domaine courant
        $domaineId = $this->container->get('request')->getSession()->get('domaineId');

        //Récupération de l'entité passée en paramètre
        $modules = $this->get('hopitalnumerique_module.manager.module')->getModuleActifForDomaine($domaineId);

        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsInscriptionOuverteModuleDomaine($this->get('request')->getSession()->get('domaineId'));

        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:index.html.twig', [
            'modules' => $modules,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Permet d'afficher les différents modules dans un menu.
     *
     * @param HopitalNumeriqueModuleBundleEntityModule $module
     *
     * @return [type]
     */
    public function showAction(\HopitalNumerique\ModuleBundle\Entity\Module $module = null)
    {
        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:show.html.twig', ['moduleSelectionne' => $module]);
    }

    /**
     * Download le fichier de session.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function downloadModuleAction(\HopitalNumerique\ModuleBundle\Entity\Module $module)
    {
        $options = [
                'serve_filename' => $module->getPath(),
                'absolute_path' => false,
                'inline' => false,
        ];

        if (file_exists($module->getUploadRootDir() . '/' . $module->getPath())) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $module->getUploadRootDir() . '/' . $module->getPath());

            return $this->get('igorw_file_serve.response_factory')->create($module->getUploadRootDir() . '/' . $module->getPath(), $mime, $options);
        } else {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add(('danger'), 'Le document n\'existe plus sur le serveur.');

            return $this->redirect($this->generateUrl('hopitalnumerique_module_module_front'));
        }
    }

    /**
     * Permet d'afficher les différents modules dans un menu.
     *
     * @param HopitalNumeriqueModuleBundleEntityModule $module
     *
     * @return [type]
     */
    public function menuModulesAction(\HopitalNumerique\ModuleBundle\Entity\Module $module = null)
    {
        //Récupération du domaine courant
        $domaineId = $this->container->get('request')->getSession()->get('domaineId');

        //Récupération de l'entité passée en paramètre
        $modules = $this->get('hopitalnumerique_module.manager.module')->getModuleActifForDomaine($domaineId);

        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:menu_modules.html.twig', [
            'modules' => $modules,
            'moduleSelectionne' => $module,
        ]);
    }
}
