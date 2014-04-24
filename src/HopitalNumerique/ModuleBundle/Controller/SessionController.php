<?php

namespace HopitalNumerique\ModuleBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Session controller.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionController extends Controller
{
    /**
     * Affiche la liste des Session.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAction(\HopitalNumerique\ModuleBundle\Entity\Module $module)
    {
        $grid = $this->get('hopitalnumerique_module.grid.session');
        $grid->setSourceCondition('module', $module->getId());

        return $grid->render('HopitalNumeriqueModuleBundle:Session:index.html.twig', array('module' => $module));
    }

    /**
     * Affiche le formulaire d'ajout de Session.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function addAction(\HopitalNumerique\ModuleBundle\Entity\Module $module)
    {
        $session = $this->get('hopitalnumerique_module.manager.session')->createEmpty();
        //Valeurs par défaut lors de la création
        $session->setModule( $module );
        $session->getDefaultValueFromModule();
        $session->setRestrictionAcces($this->get('nodevo_role.manager.role')->getRoleByArrayName(array('ROLE_AMBASSADEUR_7', 'ROLE_ARS_CMSI_4', 'ROLE_GCS_12')));

        return $this->renderForm('hopitalnumerique_module_session', $session, 'HopitalNumeriqueModuleBundle:Session:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Session.
     *
     * @param integer $id Id de Session.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $session = $this->get('hopitalnumerique_module.manager.session')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_module_session', $session, 'HopitalNumeriqueModuleBundle:Session:edit.html.twig' );
    }

    /**
     * Affiche le Session en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Session.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $session = $this->get('hopitalnumerique_module.manager.session')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueModuleBundle:Session:show.html.twig', array(
            'session' => $session,
        ));
    }

    /**
     * Suppresion d'un Session.
     * 
     * @param integer $id Id de Session.
     * METHOD = POST|DELETE
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function deleteAction( $id )
    {
        $session = $this->get('hopitalnumerique_module.manager.session')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.session')->delete( $session );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_module_module_session').'"}', 200);
    }

    /**
     * Download le fichier de session.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function downloadSessionAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        $options = array(
                'serve_filename' => $session->getPath(),
                'absolute_path'  => false,
                'inline'         => false,
        );
    
        if(file_exists($session->getUploadRootDir() . '/'. $session->getPath()))
        {
            return $this->get('igorw_file_serve.response_factory')->create( $session->getUploadRootDir() . '/'. $session->getPath(), 'application/pdf', $options);
        }
        else
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );
    
            return $this->redirect( $this->generateUrl('hopitalnumerique_module_module_session', array('id' => $session->getModule()->getId())) );
        }
    }




    /**
     * Effectue le render du formulaire Session.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Session   $entity   Entité $session
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    private function renderForm( $formName, $session, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $session);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($session->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_module.manager.session')->save($session);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Session ' . ($new ? 'ajoutée.' : 'mise à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_module_module_session', array('id' => $session->getModule()->getId())) : $this->generateUrl('hopitalnumerique_module_module_session_edit', array( 'id' => $session->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'session' => $session
        ));
    }
}