<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Module controller.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleController extends Controller
{
    /**
     * Affiche la liste des Module.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_module.grid.module');

        return $grid->render('HopitalNumeriqueModuleBundle:Back/Module:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Module.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function addAction()
    {
        $module = $this->get('hopitalnumerique_module.manager.module')->createEmpty();
        $module->setStatut($this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 3) ));

        return $this->renderForm('hopitalnumerique_module_module', $module, 'HopitalNumeriqueModuleBundle:Back/Module:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Module.
     *
     * @param integer $id Id de Module.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_module_module', $module, 'HopitalNumeriqueModuleBundle:Back/Module:edit.html.twig' );
    }

    /**
     * Affiche le Module en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Module.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueModuleBundle:Back/Module:show.html.twig', array(
            'module' => $module,
        ));
    }

    /**
     * Suppresion d'un Module.
     * 
     * @param integer $id Id de Module.
     * METHOD = POST|DELETE
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
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
     * Download le fichier de session.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function downloadModuleAction( \HopitalNumerique\ModuleBundle\Entity\Module $module )
    {
        $options = array(
            'serve_filename' => $module->getPath(),
            'absolute_path'  => false,
            'inline'         => false,
        );
    
        if(file_exists($module->getUploadRootDir() . '/'. $module->getPath()))
        {
            return $this->get('igorw_file_serve.response_factory')->create( $module->getUploadRootDir() . '/'. $module->getPath(), 'application/pdf', $options);
        }
        else
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );
    
            return $this->redirect( $this->generateUrl('hopitalnumerique_module_module') );
        }
    }




    /**
     * Effectue le render du formulaire Module.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Module   $entity   Entité $module
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    private function renderForm( $formName, $module, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $module);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) 
        {
            // On bind les données du form
            $form->handleRequest($request);

            $productions = $form->get("productions")->getData();
            if( count($productions) == 0 ) {
                $this->get('session')->getFlashBag()->add('danger', 'Veuillez sélectionner une production.');
                return $this->render( $view , array(
                    'form'   => $form->createView(),
                    'module' => $module
                ));
            }

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($module->getId());

                $module->setDateLastUpdate( new \DateTime() );
                
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
            'form'   => $form->createView(),
            'module' => $module
        ));
    }
}