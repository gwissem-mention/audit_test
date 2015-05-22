<?php

namespace HopitalNumerique\ContactBundle\Controller;

use Nodevo\ContactBundle\Controller\ContactController as NodevoController;

/**
 * Controller de Contact
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactController extends NodevoController
{
    /**
     * Controller de Contact
     *
     * @author Gaetan MELCHILSEN 
     * @copyright Nodevo
     */
    public function indexAction()
    {
        //Récupération de l'entité passée en paramètre
        $contact = $this->get('hopital_numerique_contact.manager.contact')->createEmpty();
        
        $formName         = 'hopital_numerique_contact_contact';
        $view             = 'NodevoContactBundle:Contact:index.html.twig';
        $routeRedirection = 'hopital_numerique_homepage';
        
        return $this->renderForm( $formName , $contact, $view, $routeRedirection );
    }
    
    /**
     * Effectue le render du formulaire Contractualisation.
     *
     * @param string  $formName          Nom du service associé au formulaire
     * @param Contact $contact           Entité Contractualisation
     * @param string  $view              Chemin de la vue ou sera rendu le formulaire
     * @param string  $routeRedirection  Chemin de la vue ou sera rediriger la page une fois le formulaire validé
     * @param array   $parametres        Paramètres envoyés à la vue au besoin
     *
     * @return Form | redirect
     * @author Gaetan MELCHILSEN 
     * @copyright Nodevo
     */
    private function renderForm( $formName, $contact, $view, $routeRedirection,  $parametres = array() )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $contact);
    
        $request = $this->get('request');
    
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) 
        {
            // On bind les données du form
            $form->handleRequest($request);
            
            //si le formulaire est valide
            if ($form->isValid()) 
            {
                //send Mail to all admins
                $formContact = $this->get('hopitalnumerique_contact.manager.contact')->getContactFormateMail($contact);
                
                //Récupération des destinataires dans le fichier de config
                // $mailsContact = $this->get('hopitalnumerique_contact.manager.contact')->getMailsContact();
                $mailsContact = array($this->get('hopitalnumerique_domaine.manager.domaine')->findOneById($request->getSession()->get('domaineId'))->getAdresseMailContact());

                $variablesTemplate = array(
                        'nomdestinataire'  => '',
                        'maildestinataire' => '',
                        'nomexpediteur'    => $contact->getCivilite()->getLibelle() . ' ' .$contact->getPrenom() . ' ' . $contact->getNom(),
                        'mailexpediteur'   => $contact->getMail(),
                        'contact'          => $formContact
                );
                $mailsAEnvoyer = $this->get('nodevo_mail.manager.mail')->sendContactMail($mailsContact, $variablesTemplate);

                foreach($mailsAEnvoyer as $mailAEnvoyer)
                {
                    $this->get('mailer')->send($mailAEnvoyer);
                }
                
                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopital_numerique_contact.manager.contact')->save($contact);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( 'success' , 'Votre message a bien été envoyé, nous vous recontacterons prochainement.' );

                return $this->redirect( $this->generateUrl($routeRedirection) );
            }
        }
    
        $array = array_merge(array(
                        'form'               => $form->createView(),
                        'contact'            => $contact
                ), $parametres);
    
        return $this->render( $view , $array);
    }
}
