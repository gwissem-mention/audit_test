<?php

/**
 * Controller de Contact
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
namespace Nodevo\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller de Contact
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactController extends Controller
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
        $contact = $this->get('nodevo_contact.manager.contact')->createEmpty();
        
        return $this->renderForm('nodevo_contact_contact', $contact, 'NodevoContactBundle:Contact:index.html.twig');
    }
    
    /**
     * Effectue le render du formulaire Contractualisation.
     *
     * @param string  $formName Nom du service associé au formulaire
     * @param Contact $contact  Entité Contractualisation
     * @param string  $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $contact, $view, $parametres = array() )
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
    
                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_contact.manager.contact')->save($contact);
    
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( 'success' , 'Votre message a bien été envoyé, nous vous recontacterons prochainement.' );
    
                //Sauvegarde / Sauvegarde + quitte
                $do = $request->request->get('do');
                return $this->redirect( $do == 'save-close' ? $this->generateUrl('hopitalnumerique_user_contractualisation', array('id' => $contractualisation->getUser()->getId())) : $this->generateUrl('hopitalnumerique_user_contractualisation_edit', array( 'id' => $contractualisation->getId())));
            }
        }
    
        $array = array_merge(array(
                'form'               => $form->createView(),
                'contact'            => $contact,
        ), $parametres);
    
        return $this->render( $view , $array);
    }
}
