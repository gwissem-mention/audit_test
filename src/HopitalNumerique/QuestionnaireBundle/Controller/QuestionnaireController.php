<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire as HopiQuestionnaire;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Controller des Questionnaire
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class QuestionnaireController extends Controller
{

    /**
     * Tableau de la route de redirection sous la forme :
     * array(
     *   'sauvegarde' => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
     *   'quit'       => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
     *  )
     *  
     * @var array
     */
    private $_routeRedirection = array();
    
    /**
     * Theme du formulaire utilisé
     * 
     * @var string
     */
    private $_themeQuestionnaire;

    /**
     * Envoie d'un mail de confirmation
     *
     * @var boolean
     */
    private $_envoieDeMail;
    
    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param, ajout d'une route de redirection quand tout s'est bien passé
     *
     * @param HopiUser          $user               Utilisateur courant
     * @param HopiQuestionnaire $questionnaire      Questionnaire à afficher
     * @param json              $routeRedirection   Tableau de la route de redirection une fois que le formulaire est validé
     * @param string            $themeQuestionnaire Theme de formulaire utilisé
     *
     * @return Ambigous <\HopitalNumerique\QuestionnaireBundle\Controller\Form, \Symfony\Component\HttpFoundation\RedirectResponse, \Symfony\Component\HttpFoundation\Response>
     */
    public function editFrontAction( HopiUser $user, HopiQuestionnaire $questionnaire, $optionRenderForm = array())
    {
        $readOnly            = array_key_exists('readOnly', $optionRenderForm) ? $optionRenderForm['readOnly'] : false;
        $routeRedirection    = array_key_exists('routeRedirect', $optionRenderForm) ? $optionRenderForm['routeRedirect'] : '';
        $themeQuestionnaire  = array_key_exists('themeQuestionnaire', $optionRenderForm) ? $optionRenderForm['themeQuestionnaire'] : 'default';
        $this->_envoieDeMail = array_key_exists('envoieDeMail', $optionRenderForm) ? $optionRenderForm['envoieDeMail'] : true;
        $showAllQuestions    = array_key_exists('showAllQuestions', $optionRenderForm) ? $optionRenderForm['showAllQuestions'] : true;
    
        //Si le tableau n'est pas vide on le récupère
        if(!is_null($routeRedirection))
            $this->_routeRedirection = $routeRedirection;
    
        //Récupération du thème de formulaire
        $this->_themeQuestionnaire = $themeQuestionnaire;

        $options =  array(
            'questionnaire'    => $questionnaire,
            'user'             => $user,
            'readOnly'         => $readOnly,
            'showAllQuestions' => $showAllQuestions,
            'session'          => 0
        );
    
        return $this->renderForm('nodevo_questionnaire_questionnaire', $options, 'HopitalNumeriqueQuestionnaireBundle:Questionnaire:edit_front.html.twig'
        );
    }
    
    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param, ajout d'une route de redirection quand tout s'est bien passé
     * 
     * @param HopiUser          $user               Utilisateur courant
     * @param HopiQuestionnaire $questionnaire      Questionnaire à afficher
     * @param json              $routeRedirection   Tableau de la route de redirection une fois que le formulaire est validé
     * @param string            $themeQuestionnaire Theme de formulaire utilisé
     * 
     * @return Ambigous <\HopitalNumerique\QuestionnaireBundle\Controller\Form, \Symfony\Component\HttpFoundation\RedirectResponse, \Symfony\Component\HttpFoundation\Response>
     */
    public function editAction( HopiUser $user, HopiQuestionnaire $questionnaire, $optionRenderForm = array())
    {
        $readOnly           = array_key_exists('readOnly', $optionRenderForm) ? $optionRenderForm['readOnly'] : false;
        $routeRedirection   = array_key_exists('routeRedirect', $optionRenderForm) ? $optionRenderForm['routeRedirect'] : '';
        $themeQuestionnaire = array_key_exists('themeQuestionnaire', $optionRenderForm) ? $optionRenderForm['themeQuestionnaire'] : 'default';
        $session            = array_key_exists('session', $optionRenderForm) ? $optionRenderForm['session'] : 0;

        
        //Si le tableau n'est pas vide on le récupère
        if(!is_null($routeRedirection))
            $this->_routeRedirection = $routeRedirection;
        
        //Récupération du thème de formulaire
        $this->_themeQuestionnaire = $themeQuestionnaire;
        
        return $this->renderForm('nodevo_questionnaire_questionnaire',
            array(
                    'questionnaire' => $questionnaire,
                    'user'          => $user,
                    'readOnly'      => $readOnly,
                    'session'       => $session
            ) ,
            'HopitalNumeriqueQuestionnaireBundle:Questionnaire:edit.html.twig'
        );
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
    private function renderForm( $formName, $options, $view )
    {
        $user          = $options['user'];
        $readOnly      = $options['readOnly'];
        $questionnaire = $options['questionnaire'];
        $idSession     = $options['session'];

        $label_attr = array(
                'idUser'           => $user->getId(),
                'idQuestionnaire'  => $questionnaire->getId(),
                'routeRedirection' => $this->_routeRedirection,
                'readOnly'         => $readOnly,
                'idSession'        => $idSession
        );

        if(isset($options['showAllQuestions']) && !is_null($options['showAllQuestions']))
            $label_attr['showAllQuestions'] = $options['showAllQuestions'];
    
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $questionnaire, array(
            'label_attr' => $label_attr
        ));
        
        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
    
            // On bind les données du form
            $form->handleRequest($request);
            
            $routeRedirection = json_decode($form["routeRedirect"]->getData(), true);

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
                    
                    // -v-v-v- GME 26/02/2014 : Traitement brouillon, le array_shift ou reset ne fonctionne pas -v-v-v-
                    $test = array();                 
                    foreach ($tempReponse as $temp)
                    {
                        $test[] = $temp;
                        break;
                    }                    
                    $reponse  = !empty($test) ? $test[0] : null;
                    // -^-^-^- Traitement brouillon, le array_shift ou reset ne fonctionne pas -^-^-^-
                
                    //Si il n'y a pas de réponses pour cette question pour cet utilisateur
                    if(is_null($reponse))
                    {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion($questionFiles);
                    }
                
                    //Format du champ file
                    $champFile = $questionFiles->getTypeQuestion()->getLibelle() . '_' . $questionFiles->getId() . '_' . $questionFiles->getAlias(); 

                    $file = $form[$champFile]->getData();
                    // Si le fichier n'est pas un pdf, on ne continue pas la validation du formulaire et on retourne sur celui-ci avec un message d'information
                    if ($file && $file->getMimeType() !== "application/pdf")
                    {
                        $this->get('session')->getFlashBag()->add( ('danger') , 'Vous ne pouvez uploader que des fichiers pdf pour le '. $questionFiles->getAlias() . '.' );

                        return $this->redirect( $request->headers->get('referer') );
                    }

                    $files[$questionFiles->getAlias()] = array(
                            'nom'  => $questionnaire->getNomMinifie() . '_' . $user->getId() . '_' . $user->getUsername() . '_' . $questionFiles->getAlias() . '.pdf',
                            'file' => $file,
                            'reponse' => $reponse
                    );
                    
                    //MAJ/ajout du nouveau path
                    $files[$questionFiles->getAlias()]['reponse']->setReponse($files[$questionFiles->getAlias()]['nom']);
                } 
                
                $reponses = array();
                           
                //Parcourt les fichiers uploadés
                foreach ($files as $file)
                {
                    //Si le JS est désactivé, il se peut qu'il n'y ait pas de fichier uploadé
                    if(is_null($file['file']))
                        break;
                    
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
                    $typeParam  = isset($arrayParamKey) && key_exists(0, $arrayParamKey)  ? $arrayParamKey[0] : '';
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
                    if('entity' === $typeParam)
                    {
                        $reponse->setReference($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $param)));
                    }
                    elseif('entitymultiple' === $typeParam)
                    {
                        $reponse->setReponse("");

                        $reponse->setReferenceMulitple(array());

                        foreach ($param as $value) 
                        {
                            $reponse->addReferenceMulitple($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $value)));
                        }
                    }
    
                    //Test ajout ou edition
                    $new = is_null($reponse->getId());
                    
                    //Mise à jour de la réponse dans le tableau des réponses
                    $reponses[$idQuestion] = $reponse;
                }

                if('module-evaluation' === $questionnaire->getNomMinifie())
                {

                    $idSession = $form["idSession"]->getData();

                    //Dans le cas où on est dans le formulaire de session
                    $session = ($idSession !== 0) ? $this->get('hopitalnumerique_module.manager.session')->findOneBy( array( 'id' => $idSession ) ) : null;

                    if(!is_null($session))
                    {
                        //Modifications de l'inscription: modification du statut "etatEvaluer"  
                        $inscription = $this->get('hopitalnumerique_module.manager.inscription')->findOneBy( array('user' => $user, 'session' => $session) );
                        $inscription->setEtatEvaluation( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 29)));
                        $this->get('hopitalnumerique_module.manager.inscription')->save( $inscription );
                    }
                }

                //Envoie du mail à l'utilisateur pour l'alerter de la validation de sa candidature
                if($this->_envoieDeMail)
                {
                    switch ($questionnaire->getNomMinifie())
                    {
                        case 'expert':
                            //Expert
                            $mailExpert = $this->get('nodevo_mail.manager.mail')->sendCandidatureExpertMail($user);
                            $this->get('mailer')->send($mailExpert);
    
                            //send Mail to all admins
                            $candidature = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireFormateMail($reponses);
                            $admins      = $this->get('hopitalnumerique_user.manager.user')->findUsersByRole('ROLE_ADMINISTRATEUR_1');
                            if(!is_null($admins))
                            {
                                $variablesTemplate = array(
                                    'candidat'      => $user->getPrenom() . ' ' . $user->getNom(),
                                    'questionnaire' => $candidature
                                );
                                $mailsAdmins = $this->get('nodevo_mail.manager.mail')->sendCandidatureExpertAdminMail($admins, $variablesTemplate);
                                foreach($mailsAdmins as $mailAdmins)
                                    $this->get('mailer')->send($mailAdmins);
                            }
    
                            break;
                        case 'ambassadeur':
                            //Ambassadeur
                            $mailAmbassadeur = $this->get('nodevo_mail.manager.mail')->sendCandidatureAmbassadeurMail($user);
                            $this->get('mailer')->send($mailAmbassadeur);
                            
                            //CMSI
                            $candidature = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireFormateMail($reponses);
                            
                            $etablissement = is_null($user->getEtablissementRattachementSante()) ? $user->getAutreStructureRattachementSante() : $user->getEtablissementRattachementSante()->getNom();
                            
                            $candidat = '<ul>';
                            $candidat .= '<li><strong>Prénom</strong> : ' . (trim($user->getPrenom()) === '' ? '-' : $user->getPrenom() ). '</li>';
                            $candidat .= '<li><strong>Nom</strong> : ' . (trim($user->getNom()) == '' ? '-' : $user->getNom() ). '</li>';
                            $candidat .= '<li><strong>Adresse e-mail</strong> : ' . (trim($user->getEmail()) === '' ? '-' : $user->getEmail() ). '</li>';
                            $candidat .= '<li><strong>Téléphone direct</strong> : ' . (trim($user->getTelephoneDirect()) === '' ? '-' : $user->getTelephoneDirect() ). '</li>';
                            $candidat .= '<li><strong>Téléphone portable</strong> : ' . (trim($user->getTelephonePortable()) === '' ? '-' : $user->getTelephonePortable() ). '</li>';
                            $candidat .= '<li><strong>Profil</strong> : ' . (trim($user->getProfilEtablissementSante()->getLibelle()) === '' ? '-' : $user->getProfilEtablissementSante()->getLibelle() ). '</li>';
                            $candidat .= '<li><strong>Établissement de rattrachement</strong> : ' . (trim($etablissement) === '' ? '-' : $etablissement ). '</li>';
                            $candidat .= '<li><strong>Nom de votre établissement si non disponible dans la liste précédente</strong> : ' . (trim($user->getAutreStructureRattachementSante()) === '' ? '-' : $user->getAutreStructureRattachementSante() ). '</li>';
                            $candidat .= '<li><strong>Fonction dans l\'établissement</strong> : ' . (trim($user->getFonctionStructure()) === '' ? '-' : $user->getFonctionStructure() ). '</li>';
                            $candidat .= '</ul>';

                            $CMSI        = $this->get('hopitalnumerique_user.manager.user')->findUsersByRoleAndRegion($user->getRegion(), 'ROLE_ARS_CMSI_4');
                            if(!is_null($CMSI))
                            {
                                $variablesTemplate = array(
                                    'candidat'      => $candidat,
                                    'questionnaire' => $candidature
                                );
                                $mailCMSI = $this->get('nodevo_mail.manager.mail')->sendCandidatureAmbassadeurCMSIMail($CMSI, $variablesTemplate);
                                $this->get('mailer')->send($mailCMSI);
                            }
                            break;
                        default:
                            throw new \Exception('Ce type de questionnaire ne possède pas de mail en base.');
                            break;
                    }
                }
                
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Formulaire enregistré.' );
                                
                //Mise à jour/création des réponses
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );
    
                //Sauvegarde / Sauvegarde + quitte
                $do = $request->request->get('do');
                return $this->redirect( $do == 'save-close' ? $this->generateUrl($routeRedirection['quit']['route'], $routeRedirection['quit']['arguments']) : $this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']));
            }
        }
    
        return $this->render( $view , array(
                'form'          => $form->createView(),
                'questionnaire' => $questionnaire,
                'user'          => $user,
                'theme'         => $this->_themeQuestionnaire
        ));
    }
}