<?php

namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\QuestionnaireBundle\Manager;

/**
 * Contractualisation controller.
 */
class ContractualisationController extends Controller
{    
    /**
     * Liste des contractualisations dans l'user
     *
     * @param integer $id Id de l'utilisateur
     *
     * @return Vue en liste de tous les liens de l'utilisateur $id
     */
    public function indexAction($id)
    {        
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneById($id);
        $grid = $this->get('hopitalnumerique_user.grid.contractualisation');
        $grid->setSourceCondition('user', $id);
    
        return $grid->render('HopitalNumeriqueUserBundle:Contractualisation:index.html.twig', array(
                'utilisateur' => $user,
                'options' => $this->_gestionAffichageOnglet($user)
        ));
    }

    /**
     * Affiche le formulaire d'ajout de Contractualisation.
     */
    public function addAction( $id )
    {
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->createEmpty();
        $user               = $this->get('hopitalnumerique_user.manager.user')->findOneById($id);
        $contractualisation->setUser( $user );

        return $this->_renderForm('hopitalnumerique_user_contractualisation', $contractualisation, 'HopitalNumeriqueUserBundle:Contractualisation:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Contractualisation.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->findOneBy( array('id' => $id) );

        $type_autres = $this->get('hopitalnumerique_user.options.user')->getOptionsByLabel('idTypeAutres');
        
        return $this->_renderForm('hopitalnumerique_user_contractualisation', $contractualisation, 'HopitalNumeriqueUserBundle:Contractualisation:edit.html.twig', array(
                'type_autres' => $type_autres,
            ));
    }

    /**
     * Affiche le Contractualisation en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueUserBundle:Contractualisation:show.html.twig', array(
            'contractualisation' => $contractualisation,
            'user'               => $contractualisation->getUser(),
            'options'            => $this->_gestionAffichageOnglet($contractualisation->getUser())
        ));
    }
    
    /**
     * Affiche le Contractualisation en fonction de son ID passé en paramètre.
     */
    public function dowloadAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->findOneBy( array( 'id' => $id) );
    
        $options = array(
                'serve_filename' => $contractualisation->getPath(),
                'absolute_path'  => false,
                'inline'         => false,
        );
        
        if(file_exists($contractualisation->getUploadRootDir() . '/'. $contractualisation->getPath()))
        {
            return $this->get('igorw_file_serve.response_factory')->create( $contractualisation->getUploadRootDir() . '/'. $contractualisation->getPath(), 'application/pdf', $options);
        }
        else
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );

            return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
        }        
    }

    /**
     * Suppresion d'un Contractualisation.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_user.manager.contractualisation')->delete( $contractualisation );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_user_contractualisation', array('id' => $contractualisation->getuser()->getId())).'"}', 200);
    }

    /**
     * Passe la contractualisation à "archivé".
     */
    public function archiverAction( $id )
    {
        $contractualisation = $this->get('hopitalnumerique_user.manager.contractualisation')->findOneBy( array( 'id' => $id) );

        $contractualisation->setArchiver(!$contractualisation->getArchiver());
        
        //Suppression de l'entitée
        $this->get('hopitalnumerique_user.manager.contractualisation')->save( $contractualisation );

        $this->get('session')->getFlashBag()->add('info', 'La contractualisation ' . $contractualisation->getArchiver() ? ' est archivée.' : 'n\' est plus archivée.');

        return $this->redirect( $this->generateUrl('hopitalnumerique_user_contractualisation', array('id' => $contractualisation->getuser()->getId())) );
    }

    /**
     * Liste des contractualisations dans l'user
     *
     * @param integer $id Id de l'utilisateur
     *
     * @return Vue en liste de toutes les contractualisation de l'utilisateur $id
     */
    public function listeAction( $idUser )
    {
        //récupération de l'utilisateur
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneById($idUser);
        
        //récupération des contractualisations
        $contractualisations = $this->get('hopitalnumerique_user.manager.contractualisation')->findBy(array('user' => $idUser));
        
        return $this->render('HopitalNumeriqueUserBundle:Contractualisation:liste.html.twig', array(
                'contractualisations'       => $contractualisations,
                'nombrecontractualisations' => count($contractualisations)
        ));
        
    }





    /**
     * Fonction permettant d'envoyer un tableau d'option à la vue pour vérifier le role de l'utilisateur
     *
     * @param User $user
     * @return array
     */
    private function _gestionAffichageOnglet( $user )
    {
        $options = array(
                'ambassadeur' => false,
                'expert'      => false
        );

        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = Manager\QuestionnaireManager::_getQuestionnaireId('expert');
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur');
        
        //Récupération des réponses du questionnaire expert de l'utilisateur courant
        $reponsesExpert      = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($idQuestionnaireExpert, $user->getId());
        //Récupération des réponses du questionnaire ambassadeur de l'utilisateur courant
        $reponsesAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($idQuestionnaireAmbassadeur, $user->getId());

        //Si il y a des réponses correspondant au questionnaire du groupe alors on donne l'accès
        $options['expert_form']      = !empty($reponsesExpert);
        $options['ambassadeur_form'] = !empty($reponsesAmbassadeur);
        
        //Dans tout les cas si l'utilisateur a le bon groupe on lui donne l'accès
        if( $user->hasRole('ROLE_EXPERT_6') )
            $options['expert'] = true;

        if( $user->hasRole('ROLE_AMBASSADEUR_7') )
            $options['ambassadeur'] = true;
    
        return $options;
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
    private function _renderForm( $formName, $contractualisation, $view, $parametres = array() )
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
            'options'            => $this->_gestionAffichageOnglet($contractualisation->getUser())
        ), $parametres);

        return $this->render( $view , $array);
    }
}
