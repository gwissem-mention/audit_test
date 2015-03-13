<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

    /* Gestionnaire des questionnaires */

    /**
     * Affiche la liste des questionnaires.
     */
    public function indexQuestionnaireAction()
    {
        //Récupérations de l'ensemble des questionnaires pour l'export
        $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findBy(array('lock' => false), array('nom' => 'ASC'));

        //Génération du grid
        $grid = $this->get('hopitalnumerique_questionnaire.grid.questionnaire');

        return $grid->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/index.html.twig', array(
            'questionnaires' => $questionnaires
        ));
    }

    /**
     * Editer le questionnaire.
     */
    public function editQuestionnaireAction(HopiQuestionnaire $questionnaire)
    {
        return $this->renderGestionForm('hopitalnumerique_questionnaire_gestion_questionnaire', $questionnaire, 'HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'ajout de Module.
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function addQuestionnaireAction()
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->createEmpty();

        return $this->renderGestionForm('hopitalnumerique_questionnaire_gestion_questionnaire', $questionnaire, 'HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/edit.html.twig' );
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
    public function deleteQuestionnaireAction( $id )
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array( 'id' => $id) );

        if (count($questionnaire->getOutils()) > 0)
        {
            $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible car le questionnaire est utilisé par un ou plusieurs autodiags.');
            return new Response('{"success":false, "url" : "'.$this->generateUrl('hopitalnumerique_questionnaire_index').'"}', 200);
        }
        
        //Suppression de l'entitée
        $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->delete( $questionnaire );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_questionnaire_index').'"}', 200);
    }

    /**
     * Suppression de masse des questionnaires
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1)
        {
            $rawDatas = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }        

        $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findBy( array('id' => $primaryKeys) );

        foreach ($questionnaires as $questionnaire)
        {
            if (count($questionnaire->getOutils()) > 0)
            {
                $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible car le questionnaire "'.$questionnaire->getNom().'" est utilisé par un ou plusieurs autodiags.');
                return $this->redirect( $this->generateUrl('hopitalnumerique_questionnaire_index') );
            }
        }
        
        $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->delete( $questionnaires );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_questionnaire_index') );
    }

    /**
     * Affichage du formulaire d'utilisateur
     * 
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editFrontGestionnaireAction(HopiQuestionnaire $questionnaire)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        $this->_envoieDeMail = false;
        
        return $this->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Front/index.html.twig',array(
            'questionnaire'      => $questionnaire,
            'user'               => $user,
            'optionRenderForm'   => array(
                'showAllQuestions'   => false,
                'readOnly'           => false,
                'envoieDeMail'       => true,
                'themeQuestionnaire' => 'vertical',
                'routeRedirect'      => json_encode(array(
                    'quit' => array(
                        'route'     => 'hopitalnumerique_questionnaire_edit_front_gestionnaire',
                        'arguments' => array('id' => $questionnaire->getId())
                    )
                ))
            )
        ));
    }

    /* Gestionnaire des formulaires */
    
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
     * Export CSV du questionnaire passé en paramètre
     *
     * @param HopiQuestionnaire $questionnaire Questionnaire à exporter
     *
     * @return \Symfony\Component\HttpFoundation\Response 
     */
    public function exportCSVAction( HopiQuestionnaire $questionnaire)
    {
        //Récupère tout les utilisateurs qui ont répondu à ce questionnaire
        $users = $this->get('hopitalnumerique_user.manager.user')->getUsersByQuestionnaire($questionnaire->getId());

        $results = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->buildForExport( $questionnaire->getId(), $users);
        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $results['colonnes'], $results['datas'], $questionnaire->getNom() . '-reponses.csv', $kernelCharset );
    }

    /**
     * Action appelée dans le plugin "Questionnaire" pour tinymce
     */
    public function getQuestionnairesAction()
    {
        $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findBy(array('lock' => false), array('nom' => 'ASC'));

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/getQuestionnaires.html.twig', array(
            'questionnaires' => $questionnaires,
            'texte'  => $this->get('request')->request->get('texte')
        ));
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
                    $typeParam  = isset($arrayParamKey) && array_key_exists(0, $arrayParamKey)  ? $arrayParamKey[0] : '';
                    $idQuestion = isset($arrayParamKey) && array_key_exists(1, $arrayParamKey)  ? $arrayParamKey[1] : 0;

                    //Si l'id de la question n'a pas été récupéré alors on ne sauvegarde pas la question (exemple avec le cas particulier du token du formulaire)
                    if(0 === $idQuestion || '' === $idQuestion || '_token' === $key)
                        continue;
    
                    //récupération de la réponse courante
                    $reponse = array_key_exists($idQuestion, $reponses) ? $reponses[$idQuestion] : null;
    
                    //Mode ajout
                    if(is_null($reponse))
                    {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion($this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(array('id' => $idQuestion)));
                    }
                    //Mode ajout + édition : set la nouvelle réponse
                    $reponse->setReponse($param);

                    if('entity' === $typeParam || 'entityradio' === $typeParam)
                    {
                        $reponse->setReference($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $param)));
                    }
                    elseif('entitymultiple' === $typeParam || 'entitycheckbox' === $typeParam)
                    {
                        if(is_null($reponse->getReferenceMulitple())){
                            $reponse->setReferenceMulitple(array());
                            foreach ($param as $value)
                            {
                                $reponse->addReferenceMulitple($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $value)));
                            }
                        }
                        $reponse->setReponse("");
                    }

                    if('module-evaluation' === $questionnaire->getNomMinifie())
                    {
                        $idSession = $form["idSession"]->getData();

                        if(!is_null($idSession) && 0 !== $idSession)
                        {
                            $reponse->setParamId( $idSession );
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

                        //Vérification de l'ensemble des inscriptions de la session : Si toutes les inscriptions sont évaluée alors la session est archiver
                        $sessionAArchiver = false;
                        if( $session->getDateSession() < new \DateTime() )
                        {
                            $sessionAArchiver = true;
                            foreach ($session->getInscriptions() as $inscription) 
                            {
                                if( 407 === $inscription->getEtatInscription()->getId()
                                    && 411 === $inscription->getEtatParticipation()->getId() )
                                {
                                    if( 29 !== $inscription->getEtatEvaluation()->getId() )
                                    {
                                        $sessionAArchiver = false;
                                        break;
                                    }
                                }
                            }
                        }

                        if($sessionAArchiver)
                        {
                            $session->setArchiver(true);
                            $this->get('hopitalnumerique_module.manager.session')->save( $session );
                        }

                        $this->get('hopitalnumerique_module.manager.inscription')->save( $inscription );

                        $roleUser = $this->get('nodevo_role.manager.role')->getUserRole($user);

                        //Mise à jour de la production du module dans la liste des productions maitrisées : uniquement pour les ambassadeurs
                        if('ROLE_AMBASSADEUR_7' === $roleUser)
                        {
                            //Récupération des formations
                            $formations = $session->getModule()->getProductions();
                            
                            //Pour chaque production on ajout l'utilisateur à la liste des ambassadeurs qui la maitrise
                            foreach($formations as $formation)
                            {
                                //Récupération des ambassadeurs pour vérifier si l'utilisateur actuel ne maitrise pas déjà cette formation
                                $ambassadeursFormation = $formation->getAmbassadeurs();
                                $ambassadeurIds = array();

                                foreach ($ambassadeursFormation as $ambassadeur)
                                {
                                    $ambassadeurIds[] = $ambassadeur->getId();
                                }

                                if(!in_array($user->getId(), $ambassadeurIds))
                                {
                                    $formation->addAmbassadeur( $user );
                                    $this->get('hopitalnumerique_objet.manager.objet')->save( $formation );
                                }
                            }
                        }
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
                            //Récupération de l'adresse mail en parameter.yml
                            $adressesMails = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getMailExpertReponses();
                            
                            if(!is_null($adressesMails))
                            {
                                $variablesTemplate = array(
                                    'candidat'      => $user->getPrenom() . ' ' . $user->getNom(),
                                    'questionnaire' => $candidature
                                );
                                $mailsExperts = $this->get('nodevo_mail.manager.mail')->sendCandidatureExpertAdminMail($adressesMails, $variablesTemplate);
                                foreach($mailsExperts as $mailExperts)
                                    $this->get('mailer')->send($mailExperts);
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
                            $candidat .= '<li><strong>Fonction dans l\'établissement</strong> : ' . (trim($user->getFonctionDansEtablissementSante()) === '' ? '-' : $user->getFonctionDansEtablissementSante() ). '</li>';
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
                            //Récupère les questions / réponses formatées correctement pour l'affichage dans les mails génériques
                            $candidature = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireFormateMail($reponses);
                            
                            //Formate les données de l'utilisateur qui a répondu au questionnaire
                            $etablissement = is_null($user->getEtablissementRattachementSante()) ? $user->getAutreStructureRattachementSante() : $user->getEtablissementRattachementSante()->getNom();
                            
                            $candidat = '<ul>';
                            $candidat .= '<li><strong>Prénom</strong> : ' . (trim($user->getPrenom()) === '' ? '-' : $user->getPrenom() ). '</li>';
                            $candidat .= '<li><strong>Nom</strong> : ' . (trim($user->getNom()) == '' ? '-' : $user->getNom() ). '</li>';
                            $candidat .= '<li><strong>Adresse e-mail</strong> : ' . (trim($user->getEmail()) === '' ? '-' : $user->getEmail() ). '</li>';
                            $candidat .= '<li><strong>Téléphone direct</strong> : ' . (trim($user->getTelephoneDirect()) === '' ? '-' : $user->getTelephoneDirect() ). '</li>';
                            $candidat .= '<li><strong>Téléphone portable</strong> : ' . (trim($user->getTelephonePortable()) === '' ? '-' : $user->getTelephonePortable() ). '</li>';
                            $candidat .= '<li><strong>Profil</strong> : ' . (null === $user->getProfilEtablissementSante() || trim($user->getProfilEtablissementSante()->getLibelle()) === '' ? '-' : $user->getProfilEtablissementSante()->getLibelle() ). '</li>';
                            $candidat .= '<li><strong>Établissement de rattrachement</strong> : ' . (trim($etablissement) === '' ? '-' : $etablissement ). '</li>';
                            $candidat .= '<li><strong>Nom de votre établissement si non disponible dans la liste précédente</strong> : ' . (trim($user->getAutreStructureRattachementSante()) === '' ? '-' : $user->getAutreStructureRattachementSante() ). '</li>';
                            $candidat .= '<li><strong>Fonction dans l\'établissement</strong> : ' . (trim($user->getFonctionDansEtablissementSante()) === '' ? '-' : $user->getFonctionDansEtablissementSante() ). '</li>';
                            $candidat .= '</ul>';

                            //Récupération de l'adresse mail en parameter.yml
                            $adressesMails = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getMailReponses();
                            
                            //Set des variables du gabarit du mail
                            $variablesTemplate = array(
                                'nomQuestionnaire' => $questionnaire->getNom(),
                                'candidat'         => $candidat,
                                'questionnaire'    => $candidature
                            );
                            $mailsAEnvoyer = $this->get('nodevo_mail.manager.mail')->sendReponsesQuestionnairesMail($adressesMails, $variablesTemplate);

                            foreach($mailsAEnvoyer as $mailAEnvoyer)
                                $this->get('mailer')->send($mailAEnvoyer);
                            break;
                    }
                }
                //Mise à jour/création des réponses
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );

                if(!is_null($questionnaire->getLien()) && trim($questionnaire->getLien() !== ""))
                {
                    return $this->redirect($questionnaire->getLien());
                }
                
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Formulaire enregistré.' );
    
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

    /**
     * Effectue le render du formulaire Module.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Module   $entity   Entité $questionnaire
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    private function renderGestionForm( $formName, $questionnaire, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $questionnaire);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) 
        {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($questionnaire->getId());
                
                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->save($questionnaire);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Questionnaire ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_questionnaire_index') : $this->generateUrl('hopitalnumerique_questionnaire_edit_questionnaire', array( 'id' => $questionnaire->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'          => $form->createView(),
            'questionnaire' => $questionnaire,
            'theme'         => 'vertical'
        ));
    }
}
