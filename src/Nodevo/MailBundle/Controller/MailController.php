<?php

namespace Nodevo\MailBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Mail controller.
 */
class MailController extends Controller
{
    /**
     * Affiche la liste des Mail.
     */
    public function indexAction()
    {
        $grid    = $this->get('nodevo_mail.grid.mail');
        $manager = $this->get('nodevo_mail.manager.mail');

        return $grid->render('NodevoMailBundle:Mail:index.html.twig', array(
                'allowAdd' => $manager->isAllowedToAdd(),
            )
        );
    }

    /**
     * Affiche le formulaire d'ajout de Mail.
     */
    public function addAction()
    {
        $mail = $this->get('nodevo_mail.manager.mail')->createEmpty();

        return $this->renderForm('nodevo_mail_mail', $mail, 'NodevoMailBundle:Mail:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Mail.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $mail = $this->get('nodevo_mail.manager.mail')->findOneBy( array('id' => $id) );

        return $this->renderForm('nodevo_mail_mail', $mail, 'NodevoMailBundle:Mail:edit.html.twig' );
    }

    /**
     * Affiche le Mail en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $mail = $this->get('nodevo_mail.manager.mail')->findOneBy( array( 'id' => $id) );

        return $this->render('NodevoMailBundle:Mail:show.html.twig', array(
            'mail'        => $mail,
            'allowDelete' => $this->get('nodevo_mail.manager.mail')->isAllowedToDelete()
        ));
    }

    /**
     * Suppresion d'un Mail.
     *
     * @METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $mail = $this->get('nodevo_mail.manager.mail')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('nodevo_mail.manager.mail')->delete( $mail );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('nodevo_mail_mail').'"}', 200);
    }

    /**
     * Effectue le render du formulaire Mail.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Mail   $mail     Entité Mail
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $mail, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $mail);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($mail->getId()) ? true : false;

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_mail.manager.mail')->save($mail);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Template d\'e-mail ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                $do = $request->request->get('do');
                return $this->redirect( ( $do == 'save-close' ? $this->generateUrl('nodevo_mail_mail') : $this->generateUrl('nodevo_mail_mail_edit', array( 'id' => $mail->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'        => $form->createView(),
            'mail'        => $mail,
            'allowDelete' => $this->get('nodevo_mail.manager.mail')->isAllowedToDelete()
        ));
    }

    /**
     * Test d'envoi d'un Mail.
     *
     */
    public function sendTestAction( $id )
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $mail = $this->get('nodevo_mail.manager.mail')->getMessageTest( $id, $user );

        $this->get('mailer')->send($mail);

        $message = 'Message de test envoyé à '.$this->get('nodevo_mail.manager.mail')->getDestinataire();
        $this->get('session')->getFlashBag()->add('info', $message);

        return $this->redirect($this->generateUrl('nodevo_mail_mail_show', array( 'id' => $id)));
    }
}