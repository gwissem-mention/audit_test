<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire as HopiQuestionnaire;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class QuestionnaireController extends Controller
{
    public function editAction( HopiUser $user, HopiQuestionnaire $questionnaire )
    {
        return $this->_renderForm('nodevo_questionnaire_questionnaire',
                array(
                        'questionnaire' => $questionnaire,
                        'user'          => $user
                ) ,
                'HopitalNumeriqueQuestionnaireBundle:Questionnaire:edit.html.twig'
        );
    }

    public function deleteAllAction()
    {
    }

    public function downloadAction()
    {
    }

    /**
     * Fonction permettant d'envoyer un tableau d'option à la vue pour vérifier le role de l'utilisateur
     *
     * @param User $user
     * @return array
     */
    private function _gestionAffichageOnglet( $user )
    {
        $roles = $user->getRoles();
        $options = array(
                'ambassadeur' => false,
                'expert'      => false
        );
    
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = QuestionnaireManager::_getQuestionnaireId('expert');
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = QuestionnaireManager::_getQuestionnaireId('ambassadeur');
    
        //Récupération des réponses du questionnaire expert de l'utilisateur courant
        $reponsesExpert      = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($idQuestionnaireExpert, $user->getId());
        //Récupération des réponses du questionnaire ambassadeur de l'utilisateur courant
        $reponsesAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($idQuestionnaireAmbassadeur, $user->getId());
    
        //Si il y a des réponses correspondant au questionnaire du groupe alors on donne l'accès
        $options['expert_form']      = !empty($reponsesExpert);
        $options['ambassadeur_form'] = !empty($reponsesAmbassadeur);
    
        //Dans tout les cas si l'utilisateur a le bon groupe on lui donne l'accès
        foreach ($roles as $key => $role)
        {
            switch ($role->getRole())
            {
            	case 'ROLE_EXPERT_6':
            	    $options['expert'] = true;
            	    break;
            	case 'ROLE_AMBASSADEUR_7':
            	    $options['ambassadeur'] = true;
            	    break;
            	default:
            	    break;
            }
        }
    
        return $options;
    }
    
    /**
     * Effectue le render des formulaires de Questionnaire
     *
     * @param string $formName Nom du service associé au formulaire
     * @param array(Entity) $options Tableau d'entité necessaire à l'affichage
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function _renderForm( $formName, $options, $view )
    {
        $user          = $options['user'];
        $questionnaire = $options['questionnaire'];
    
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $questionnaire, array(
                'label_attr' => array(
                        'idUser' => $user->getId(),
                        'idQuestionnaire' => $questionnaire->getId()
                )
        ));
        
        $request = $this->get('request');
    
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
    
            // On bind les données du form
            $form->handleRequest($request);
            //si le formulaire est valide
            if ($form->isValid()) {
    
                //Les champs file uploadés ne sont pas dans params, params ne recupère que les inputs
                $params = $request->get('nodevo_questionnaire_questionnaire');
                
                //Récupèrations des questions de type files pour le questionnaire courant
                $questionsFiles = $this->get('hopitalnumerique_questionnaire.manager.question')->getQuestionsByType( $questionnaire->getId() , 'file' );
                
                //Gestion des files uploadés
                $dossierRoot = $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir($questionnaire->getNomMinifie());
                $files = array();
                
                //get All References, and convert to ArrayCollection
                $reponses = new ArrayCollection( $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUserByFileQuestion( $questionnaire->getId(), $user->getId(), false ) );
             
                //Parcourt les questions de champ file
                foreach ($questionsFiles as $key => $questionFiles)
                {   
                    //Récupère la réponse de la question courante
                    $criteria = Criteria::create()->where(Criteria::expr()->eq("question", $questionFiles) );
                    //Récupération d'un tableau comportant une seule réponse
                    $tempReponse = $reponses->matching( $criteria );
                    $reponse  = $tempReponse[0];
                
                    //Si il n'y a pas de réponses pour cette question pour cet utilisateur
                    if(is_null($reponse))
                    {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion($questionFiles);
                    }
                
                    //Format du champ file
                    $champFile = $questionFiles->getTypeQuestion()->getLibelle() . '_' . $questionFiles->getId() . '_' . $questionFiles->getAlias();
                    $files[$questionFiles->getAlias()] = array(
                            'nom'  => $questionnaire->getNomMinifie() . '_' . $user->getId() . '_' . $user->getNom() . '_' . $user->getPrenom() . '_' . $questionFiles->getAlias() . '.pdf',
                            'file' => $form[$champFile]->getData(),
                            'reponse' => $reponse
                    );
                    
                    //MAJ/ajout du nouveau path
                    $files[$questionFiles->getAlias()]['reponse']->setReponse($files[$questionFiles->getAlias()]['nom']);
                                                                 //->setPath($files[$questionFiles->getAlias()]['nom']);
                
                    //Vérification si le file est obligatoire et renseigné
                    if( $questionFiles->getObligatoire() && is_null($files[$questionFiles->getAlias()]['file']))
                    {
                        $this->get('session')->getFlashBag()->add('danger' ,  'Le champ '. $questionFiles->getAlias() .' est obligatoire.' );
                
                        return $this->render('HopitalNumeriqueUserBundle:Expert:edit.html.twig',array(
                                'questionnaire' => $questionnaire,
                                'user'          => $user,
                                'options' => $this->_gestionAffichageOnglet($user)
                        ));
                    }
                    //Vérification du mimetype et de la taille (10Mo)
                    elseif( !is_null($files[$questionFiles->getAlias()]['file'])
                            && ('application/pdf' !== $files[$questionFiles->getAlias()]['file']->getMimeType() || 10000000 < $files[$questionFiles->getAlias()]['file']->getSize())
                    )
                    {
                        $this->get('session')->getFlashBag()->add('danger' ,  ('application/pdf' !== $files[$questionFiles->getAlias()]['file']->getMimeType() ? 'Le fichier attendu pour votre '. $questionFilesExpert->getAlias() .' doit être un pdf.' : 'La taille de votre CV est trop volumineuse, 10Mo maximum.' ));
                
                        return $this->render('HopitalNumeriqueUserBundle:Expert:edit.html.twig',array(
                                'questionnaire' => $questionnaire,
                                'user'          => $user,
                                'options' => $this->_gestionAffichageOnglet($user)
                        ));
                    }
                }    
                
                $reponses = array();
            
                //Parcourt les fichiers uploadés
                foreach ($files as $file)
                {
                    $file['file']->move($dossierRoot, $file['nom']);
        
                    $reponses[] = $file['reponse'];
                }
                    
                //Mise à jour / créations des réponses correspondantent aux fichiers
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );
                
                //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
                $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $questionnaire->getId(), $user->getId(), true );
                
                //Gestion des réponses
                foreach ($params as $key => $param)
                {
                    //Récupération de l'id de la question, la clé est sous la forme : "type_id_alias"
                    $arrayParamKey = explode('_', $key);
    
                    //Le tableau de arrayParamKey : 0 => type du champ - 1 => Id de la question - 2+=> alias du champ
                    $idQuestion = isset($arrayParamKey) && key_exists(1, $arrayParamKey)  ? $arrayParamKey[1] : 0;
    
                    //Si l'id de la question n'a pas été récupéré alors on ne sauvegarde pas la question (exemple avec le cas particulier du token du formulaire)
                    if(0 === $idQuestion || '' === $idQuestion || '_token' === $key)
                        continue;
    
                    //récupération de la réponse courante
                    $reponse = key_exists($idQuestion, $reponses) ? $reponses[$idQuestion] : null;    
    
                    //Mode ajout
                    if(is_null($reponse))
                    {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion($this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(array('id' => $idQuestion)));
                    }
                    //Mode ajout + édition : set la nouvelle réponse
                    $reponse->setReponse($param);
    
                    //Test ajout ou edition
                    $new = is_null($reponse->getId()) ? true : false;
                    
                    //Mise à jour de la réponse dans le tableau des réponses
                    $reponses[$idQuestion] = $reponse;
                }
                
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Candidature ' . $questionnaire->getNomMinifie() . ' ' . ($new ? 'créée.' : 'mise à jour.') );
                
                //Mise à jour/création des réponses
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );
    
                //Sauvegarde / Sauvegarde + quitte
                $do = $request->request->get('do');
                return $this->redirect( $do == 'save-close' ? $this->generateUrl('hopital_numerique_user_homepage') : $this->generateUrl('hopitalnumerique_user_expert_edit', array( 'id' => $user->getId())));
            }
        }
    
        return $this->render( $view , array(
                'form' => $form->createView(),
                'questionnaire' => $questionnaire,
                'user' => $user
        ));
    }
    
    /**
     * Fonction permettant de gerer les files des questionnaire
     * 
     * @param HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire
     * @param HopitalNumerique\UserBundle\Entity\User                   $user
     * @param Formulaire                                                $form
     * @param string                                                    $view     Chemin de la vue ou sera rendu le formulaire
     */
    public function _gestionFile( HopiQuestionnaire $questionnaire, HopiUser $user, $form, $view)
    {
                
    }
    
}
