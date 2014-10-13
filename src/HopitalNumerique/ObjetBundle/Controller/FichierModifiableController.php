<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ObjetBundle\Entity\Objet;

class FichierModifiableController extends Controller
{
    /**
     * Formulaire d'edition d'une entité fichier modifiable liée à l'objet
     *
     * @return [type]
     */
    public function indexAction(Objet $objet)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        //Création du formulaire via le service
        $form = $this->createForm('nodevo_user_motdepasse', $user);
        
        $view = 'HopitalNumeriqueUserBundle:User/Front:motdepasse.html.twig';
        
        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);
            
            //si le formulaire est valide
            if ($form->isValid())
            {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                
                //Vérifie si le mot de passe entré dans le formulaire correspondant au mot de passe de l'utilisateur
                if($encoder->isPasswordValid($user->getPassword(), $form->get("oldPassword")->getData(), $user->getSalt()))
                {
                    //Mise à jour / création de l'utilisateur
                    $this->get('fos_user.user_manager')->updateUser( $user );

                    // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                    $this->get('session')->getFlashBag()->add('success', 'Mot de passe mis à jour.');
                    
                    return $this->redirect( $this->generateUrl('hopital_numerique_user_informations_personnelles') );
                }
                else
                {
                    // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                    $this->get('session')->getFlashBag()->add('danger', 'L\'ancien mot de passe saisi est incorrect.');
                    
                    return $this->redirect( $this->generateUrl('hopital_numerique_user_motdepasse') );
                }
                
            }
        }

        return $this->render( $view , array(
            'form'        => $form->createView(),
            'user'        => $user,
            'options'     => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user)
        ));
    }
}
