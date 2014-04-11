<?php

namespace HopitalNumerique\ModuleBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Module controller.
 */
class ModuleController extends Controller
{
    /**
     * Affiche la liste des Module.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_module.grid.module');

        return $grid->render('HopitalNumeriqueModuleBundle:Module:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Module.
     */
    public function addAction()
    {
        $module = $this->get('hopitalnumerique_module.manager.module')->createEmpty();

        return $this->renderForm('hopitalnumerique_module_module', $module, 'HopitalNumeriqueModuleBundle:Module:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Module.
     *
     * @param integer $id Id de Module.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_module_module', $module, 'HopitalNumeriqueModuleBundle:Module:edit.html.twig' );
    }

    /**
     * Affiche le Module en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Module.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueModuleBundle:Module:show.html.twig', array(
            'module' => $module,
        ));
    }

    /**
     * Suppresion d'un Module.
     * 
     * @param integer $id Id de Module.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.module')->delete( $module );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_module_module').'"}', 200);
    }





    /**
     * Effectue le render du formulaire Module.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Module   $entity   Entité $module
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $module, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $module);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($module->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_module.manager.module')->save($module);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Module ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_module_module') : $this->generateUrl('hopitalnumerique_module_module_edit', array( 'id' => $module->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'module' => $module
        ));
    }
}