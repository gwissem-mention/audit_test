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
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->findOneBy( array('id' => $id) );

        $type_autres = $this->get('hopitalnumerique_user.options.user')->getOptionsByLabel('idTypeAutres');
        
        return $this->_renderForm('hopitalnumerique_user_contractualisation', $contractualisation, 'HopitalNumeriqueUserBundle:Contractualisation:edit.html.twig', array(
                'type_autres' => $type_autres,
        ));
    }
    
    /**
     * Effectue le render du formulaire Contractualisation.
     *
     * @param string             $formName           Nom du service associé au formulaire
     * @param Contractualisation $contractualisation Entité Contractualisation
     * @param string             $view               Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $contractualisation, $view, $parametres = array() )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $contractualisation);
    
        $request = $this->get('request');
    
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);
    
            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($contractualisation->getId()) ? true : false;
    
                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_user.manager.contractualisation')->save($contractualisation);
    
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Contractualisation ' . ($new ? 'ajouté.' : 'mis à jour.') );
    
                //Sauvegarde / Sauvegarde + quitte
                $do = $request->request->get('do');
                return $this->redirect( $do == 'save-close' ? $this->generateUrl('hopitalnumerique_user_contractualisation', array('id' => $contractualisation->getUser()->getId())) : $this->generateUrl('hopitalnumerique_user_contractualisation_edit', array( 'id' => $contractualisation->getId())));
            }
        }
    
        $array = array_merge(array(
                'form'               => $form->createView(),
                'contractualisation' => $contractualisation,
                'user'               => $contractualisation->getUser(),
                'options'            => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($contractualisation->getUser())
        ), $parametres);
    
        return $this->render( $view , $array);
    }
}
