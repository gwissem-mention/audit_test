<?php
namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller des utilisateurs
 */
class UserController extends Controller
{    
    /**
     * Vue informations personnelles sur le front
     * 
     * @var boolean
     */
    protected $_informationsPersonnelles = false;
    
    //---- Front Office ------
    /**
     * Affichage du formulaire d'inscription
     */
    public function inscriptionAction()
    {
        //Si il n'y a pas d'utilisateur connecté
        if(!$this->get('security.context')->isGranted('ROLE_USER'))
        {
            //Récupération de l'utilisateur passé en param
            $user = $this->get('hopitalnumerique_user.manager.user')->createEmpty();
             
            return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User/Front:inscription.html.twig');
        }
    
        return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
    }
    
    /**
     * Affichage du formulaire d'utilisateur
     */
    public function informationsPersonnellesAction()
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        $this->_informationsPersonnelles = true;
             
        return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User/Front:informations_personnelles.html.twig');
    }
    
    /**
     * Affichage du formulaire de modification du mot de passe
     *
     * @param integer $id Identifiant de l'utilisateur
     */
    public function motDePasseAction()
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
        return $this->_renderView( $view , $form, $user);
    }
    

    //---- Back Office ------    
    /**
     * Affichage des utilisateurs
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_user.grid.user');

        return $grid->render('HopitalNumeriqueUserBundle:User:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout d'utilisateur
     */
    public function addAction()
    {
        $user = $this->get('hopitalnumerique_user.manager.user')->createEmpty();

        return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:edit.html.twig' );
    }

    /**
     * Affichage du formulaire d'utilisateur
     * 
     * @param intger $id Identifiant de l'utilisateur
     */
    public function editAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:edit.html.twig' );
    }
    
    /**
     * Affichage de la fiche d'un utilisateur
     * 
     * @param integer $id ID de l'utilisateur
     */
    public function showAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user  = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );
        $roles = $this->get('nodevo_role.manager.role')->findIn( $user->getRoles() );

        return $this->render('HopitalNumeriqueUserBundle:User:show.html.twig', array(
            'user'                     => $user,
            'questionnaireExpert'      => $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('expert'),
            'questionnaireAmbassadeur' => $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur'),
            'options'                  => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'roles'                    => $roles
        ));
    }

    /**
     * Suppression d'un utilisateur
     *
     * @param integer $id ID de l'utilisateur
     */
    public function deleteAction( $id )
    {
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );
        
        //L'utilisateur super admin est par défaut à l'id 1, il ne peut jamais être supprimé
        if( !$user->getLock() ) {
            //Suppression de l'utilisateur
            $this->get('hopitalnumerique_user.manager.user')->delete( $user );
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
        }else
            $this->get('session')->getFlashBag()->add('danger', 'Vous ne pouvez pas supprimer un utilisateur vérouillé.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_user_homepage').'"}', 200);
    }

    /**
     * Génère la liste des département en fonction de l'id de la région
     */
    public function ajaxEditDepartementsAction()
    {
        $id             = $this->get('request')->request->get('id');
        $departements   = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array('parent' => $id) );
    
        return $this->render('HopitalNumeriqueUserBundle:User:departements.html.twig', array(
            'departements' => $departements
        ));
    }
    
    /**
     * Génère la liste des établissement en fonction de l'id du département
     */
    public function ajaxEditEtablissementsAction()
    {
        $idDepartement        = $this->get('request')->request->get('idDepartement');
        $idTypeEtablissement  = $this->get('request')->request->get('idTypeEtablissement');
        //Par défaut le département est obligatoire
        $where = array(
        	'departement' => $idDepartement
        );
        
        //Si il y a un type établissement on rajoute filtre
        if(!empty($idTypeEtablissement))
        {
            $where['typeOrganisme'] = $idTypeEtablissement;
        }
        
        $etablissements = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findBy( $where );
    
        return $this->render('HopitalNumeriqueUserBundle:User:etablissements.html.twig', array(
                'etablissements' => $etablissements
        ));
    }

    /**
     * Suppression de masse des users
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //check connected user ACL
        $user = $this->get('security.context')->getToken()->getUser();

        if( $this->get('nodevo_acl.manager.acl')->checkAuthorization( $this->generateUrl('hopital_numerique_user_delete', array('id'=>1)) , $user ) == -1 ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas les droits suffisants pour supprimer des utilisateurs.' );
            return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
        }

        //get all selected Users
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys, 'lock' => 0) );
        $this->get('hopitalnumerique_user.manager.user')->delete( $users );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
    }

    /**
     * Désactivation de masse des users
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function desactiverMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //check connected user ACL
        $user = $this->get('security.context')->getToken()->getUser();

        if( $this->get('nodevo_acl.manager.acl')->checkAuthorization( $this->generateUrl('hopital_numerique_user_delete', array('id'=>1)) , $user ) == -1 ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas les droits suffisants pour désactiver des utilisateurs.' );
            return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
        }

        //get all selected Users
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys, 'lock' => 0) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 4) );
        $this->get('hopitalnumerique_user.manager.user')->toogleState( $users, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Désactivation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
    }

    /**
     * Activation de masse des users
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function activerMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //check connected user ACL
        $user = $this->get('security.context')->getToken()->getUser();

        if( $this->get('nodevo_acl.manager.acl')->checkAuthorization( $this->generateUrl('hopital_numerique_user_delete', array('id'=>1)) , $user ) == -1 ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas les droits suffisants pour activer des utilisateurs.' );
            return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
        }

        //get all selected Users
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys, 'lock' => 0) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 3) );
        $this->get('hopitalnumerique_user.manager.user')->toogleState( $users, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Activation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
		if($allPrimaryKeys == 1)
			$users = $this->get('hopitalnumerique_user.manager.user')->findAll();
		else
			$users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );

        // Pour Excel Windows, les deux premiers caractères doivent être en minuscules,
		// sinon le fichier est reconnu en tant que fichier SYLK.
		//
		// Un fichier SYLK est un fichier texte qui commence par « ID » ou « ID_xxxx »,
		// où xxxx est une chaîne de texte. Le premier enregistrement d'un fichier
		// SYLK est ID_Number. Lorsqu'Excel identifie ce texte au début d'un fichier texte,
		// il interprète le fichier en tant que fichier SYLK. Excel essaie de convertir
		// le fichier à partir du format SYLK, mais il n'y parvient pas car aucun code
		// SYLK ne figure après les caractères « ID ». Étant donné qu'Excel ne peut pas
		// convertir le fichier, le message d'erreur d'affiche. 
		$colonnes = array( 
                            'id'                  => 'id', 
                            'nom'                 => 'Nom', 
                            'prenom'              => 'Prénom', 
                            'username'            => 'Nom du compte', 
                            'email'               => 'Adresse e-mail',
                            'etat.libelle'        => 'Etat',
                            'region.libelle'      => 'Région',
                            'titre.libelle'       => 'Titre',
                            'civilite.libelle'    => 'Civilité',
                            'telephoneDirect'     => 'Téléphone Direct',
                            'telephonePortable'   => 'Téléphone Portable',
                            'departement.libelle' => 'Département'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $users, $kernelCharset );
    }

	/**
     * Envoyer un mail aux utilisateurs
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function envoyerMailMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1)
            $users = $this->get('hopitalnumerique_user.manager.user')->findAll();
        else
            $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
        
        //get emails
        $list = array();
        foreach($users as $user)
            if($user->getEmail() != "")
                $list[] = $user->getEmail();

        //to
        $to = $this->get('security.context')->getToken()->getUser()->getEmail();
        
        //bcc list
        $bcc = join(';', $list);
        
        return $this->render('HopitalNumeriqueUserBundle:User:mailto.html.twig', array(
            'mailto' => 'mailto:'.$to.'?bcc='.$bcc
        ));
    }
	








    
    /**
     * Effectue le render du formulaire Utilisateur
     *
     * @param string $formName Nom du service associé au formulaire
     * @param User   $entity   Entité utilisateur
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $user, $view )
    {        
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $user);
        
        //Si on est en FO dans informations personelles, on affiche pas le mot de passe. Il est géré dans un autre formulaire
        if($this->_informationsPersonnelles)
            $form->remove('plainPassword');

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
                        
            // On bind les données du form
            $form->handleRequest($request);

            //Vérification d'un utilisateur connecté
            if($this->get('security.context')->isGranted('ROLE_USER'))
            {
                //Si un utilisateur est connecté mais qu'on est en FO : informations personnelles
                if(!$this->_informationsPersonnelles)
                {
                    //--Backoffice--
                    //Vérification de la présence rôle
                    $role = $form->get("roles")->getData();
                    if(is_null($role)) {
                        $this->get('session')->getFlashBag()->add('danger', 'Veuillez sélectionner un groupe associé.');
                    
                        return $this->_renderView( $view , $form, $user);
                    }                    
                }
            }
            else
            {    
                //--FO-- inscription            
                //Set de l'état
                $idEtatActif = intval($this->get('hopitalnumerique_user.options.user')->getOptionsByLabel('idEtatActif'));
                $user->setEtat($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $idEtatActif)));
            }
            
            //si le formulaire est valide
            if ($form->isValid()) 
            {
                //test ajout ou edition
                $new = is_null($user->getId());

                //Generate password for new users
                if( $new ) {
                    $passwordTool = new \Nodevo\ToolsBundle\Tools\Password();
                    $mdp = $passwordTool->generate(3,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                    $mdp .= $passwordTool->generate(3,'abcdefghijklmnopqrstuvwyyz');
                    $mdp .= $passwordTool->generate(2,'1234567890');
                    $mdp = str_shuffle($mdp);
                    $user->setPlainPassword( $mdp );
                    $user->setDateInscription( new \DateTime() );

                    //Différence entre le FO et BO : vérification qu'il y a un utilisateur connecté
                    if($this->get('security.context')->isGranted('ROLE_USER'))
                    {
                        //--BO--
                        $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserFromAdminMail($user);
                        $this->get('mailer')->send($mail);
                    }
                    else
                    {
                        //--FO--
                        $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserMail($user);
                        $this->get('mailer')->send($mail);
                    }
                }

                //Vérification d'un utilisateur connecté
                if($this->get('security.context')->isGranted('ROLE_USER'))
                {
                    if($this->_informationsPersonnelles)
                    {
                        //--Frontoffice-- Informations personnelles
                        //Reforce le role de l'utilisateur pour éviter qu'il soit modifié
                        $connectedUser = $this->get('security.context')->getToken()->getUser();
                        $roleUserConnectedLabel = $this->get('nodevo_role.manager.role')->getUserRole($connectedUser);
                        $role = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => $roleUserConnectedLabel));
                        $user->setRoles( array( $role ) );
                        
                        //Reforce l'username
                        $user->setUsername($user->getUsername());
                    }
                    else 
                    {
                        //--BO--
                        //set Role for User : not mapped field
                        $user->setRoles( array( $role->getRole() ) );
                    }
                }
                else
                {
                    //--FO-- Inscription
                    //Set du role "Enregistré" par défaut pour les utilisateurs
                    $role = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_ENREGISTRE_9'));
                    $user->setRoles( array( $role->getRole() ) );
                }

                //Cas particulier: 1 utilisateur par groupe ARS-CMSI par région 
                //Dans le cas où la région est à nulle il n'y a pas besoin de vérifier si il existe déjà un ARS pour cette région.
                if(null != $user->getRegion() && $role->getRole() == 'ROLE_ARS_CMSI_4' )
                {
                    //On vérifie que l'utilisateur ayant le rôle ROLE_ARS_CMSI_4 n'a pas non plus la même région sinon on return et pas de sauvegarde
                    $result = $this->get('hopitalnumerique_user.manager.user')->userExistForRoleArs( $user );

                    if( ! is_null($result) ) {
                        $this->get('session')->getFlashBag()->add('danger', 'Il existe déjà un utilisateur associé au groupe ARS-CMSI pour cette région.' );
                
                        return $this->_renderView( $view , $form, $user);
                    }
                }
                else if ( null == $user->getRegion() )
                {
                    //Cas particuliers : La région est obligatoire pour les roles ARS-CMSI et Ambassadeur
                    if( $role->getRole() == 'ROLE_ARS_CMSI_4' || $role->getRole() == 'ROLE_AMBASSADEUR_7') {
                        $this->get('session')->getFlashBag()->add('danger', 'Il est obligatoire de choisir une région pour le groupe sélectionné.' );
                        
                        return $this->_renderView( $view , $form, $user);
                    }
                }
                
                //Cas particulier : 2 utilisateur ES - Direction générale par établissement de rattachement
                if( null != $user->getEtablissementRattachementSante() && $role->getRole() == 'ROLE_ES_DIRECTION_GENERALE_5')
                {
                    $result = $this->get('hopitalnumerique_user.manager.user')->userExistForRoleDirection( $user );
                    if( ! is_null($result) ) {
                        $this->get('session')->getFlashBag()->add('danger', 'Il existe déjà un utilisateur associé au groupe Direction générale pour cet établissement.');
                    
                        return $this->_renderView( $view , $form, $user);
                    }
                }
                
                //bind Référence Etat with Enable FosUserField
                if( intval($this->get('hopitalnumerique_user.options.user')->getOptionsByLabel('idEtatActif')) === $user->getEtat()->getId())
                    $user->setEnabled( 1 );
                else
                    $user->setEnabled( 0 );

                //Mise à jour / création de l'utilisateur
                $this->get('fos_user.user_manager')->updateUser( $user );
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Utilisateur ' . $user->getUsername() . ($new ? ' ajouté.' : ' mis à jour.') ); 
                
                $do = $request->request->get('do');
                
                switch ($do)
                {
                	case 'inscription':
                	    return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
                	    break;
                	case 'information-personnelles':
                	    return $this->redirect( $this->generateUrl('hopital_numerique_user_informations_personnelles') );
                	    break;
                	case 'save-close':
                	    return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
                	    break;
                	default:
                	    return $this->redirect( $this->generateUrl('hopital_numerique_user_edit', array( 'id' => $user->getId())) );
                	    break;
                }
            }
        }
        
        return $this->_renderView( $view , $form, $user);
    }

    private function _renderView( $view, $form, $user )
    {
        return $this->render( $view , array(
            'form'    => $form->createView(),
            'user'    => $user,
            'options' => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user)
        ));
    }
}
