<?php
namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;
use Nodevo\RoleBundle\Entity\Role;
use HopitalNumerique\QuestionnaireBundle\Manager;
use HopitalNumerique;

/**
 * Controller des utilisateurs
 * 
 * @author Quentin SOMAZZI
 * @copyright Nodevo
 */
class UserController extends Controller
{
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

        return $this->_renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:edit.html.twig' );
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

        return $this->_renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:edit.html.twig' );
    }
    
    /**
     * Affichage du formulaire d'inscription
     */
    public function inscriptionAction()
    {       
        //On récupère l'user connecté et son role
        $user  = $this->get('security.context')->getToken()->getUser();
        
        
        //Si il n'y a pas d'utilisateur connecté
        if(!$this->get('security.context')->isGranted('ROLE_USER'))
        {
            //Récupération de l'utilisateur passé en param
            $user = $this->get('hopitalnumerique_user.manager.user')->createEmpty();
             
            return $this->_renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:inscription.html.twig');            
        }
        
        return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
    }

    /**
     * Affichage de la fiche d'un utilisateur
     * 
     * @param integer $id ID de l'utilisateur
     */
    public function showAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );
        $roles = $this->get('nodevo_role.manager.role')->findIn( $user->getRoles() );

        return $this->render('HopitalNumeriqueUserBundle:User:show.html.twig', array(
            'user'                     => $user,
            'questionnaireExpert'      => HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager::_getQuestionnaireId('expert'),
            'questionnaireAmbassadeur' => HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur'),
            'options'                  => $this->_gestionAffichageOnglet($user),
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
        $id              = $this->get('request')->request->get('id');
        $etablissements  = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findBy( array('departement' => $id) );
    
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
    public function ExportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );

        $colonnes = array( 
                            'id'                  => 'ID', 
                            'nom'                 => 'Nom', 
                            'prenom'              => 'Pr&eacute;nom', 
                            'username'            => 'Nom d\'utilisateur', 
                            'email'               => 'Adresse e-mail',
                            'etat.libelle'        => 'Etat',
                            'region.libelle'      => 'R&eacute;gion',
                            'titre.libelle'       => 'Titre',
                            'civilite.libelle'    => 'Civilit&eacute;',
                            'telephoneDirect'     => 'T&eacute;l&eacute;phone Direct',
                            'telephonePortable'   => 'T&eacute;l&eacute;phone Portable',
                            'departement.libelle' => 'D&eacute;partement'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $users, $kernelCharset );
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
    private function _renderForm( $formName, $user, $view )
    {        
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $user);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
                        
            // On bind les données du form
            $form->handleRequest($request);

            //Différence entre le FO et BO : vérification qu'il y a un utilisateur connecté
            if($this->get('security.context')->isGranted('ROLE_USER'))
            {            
                //--Backoffice--
                //Vérification de la présence rôle
                $role = $form->get("roles")->getData();
                if(is_null($role)) {
                    $this->get('session')->getFlashBag()->add('danger', 'Veuillez sélectionner un groupe associé.');
                
                    return $this->_renderView( $view , $form, $user);
                }
            }
            else
            {
                //L'username = l'adresse mail de l'utilisateur
                $user->setUsername($user->getEmail());
                
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

                    //Différence entre le FO et BO : vérification qu'il y a un utilisateur connecté
                    if($this->get('security.context')->isGranted('ROLE_USER'))
                    {
                        //--BO--
                        //set Role for User : not mapped field
                        $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserFromAdminMail($user);
                        $this->get('mailer')->send($mail);
                    }
                    else
                    {
                        //--FO--
                        //Set du role "Enregistré" par défaut pour les utilisateurs
                        $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserMail($user);
                        $this->get('mailer')->send($mail);
                    }
                }

                //Différence entre le FO et BO : vérification qu'il y a un utilisateur connecté
                if($this->get('security.context')->isGranted('ROLE_USER'))
                {
                    //--BO--
                    //set Role for User : not mapped field
                    $user->setRoles( array( $role->getRole() ) );
                }
                else
                {
                    //--FO--
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
                if( null != $user->getEtablissementRattachementSante() )
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
                	case 'front':
                	    return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
                	    break;
                	case 'save-close':
                	    return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
                	    break;
                	default:
                	    return $this->redirect( $this->generateUrl('hopital_numerique_user_edit', array( 'id' => $user->getId())) );
                	    break;
                }
                //return $this->redirect( ( $do == 'save-close' ? $this->generateUrl('hopital_numerique_user_homepage') : $this->generateUrl('hopital_numerique_user_edit', array( 'id' => $user->getId() ) ) ) );
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