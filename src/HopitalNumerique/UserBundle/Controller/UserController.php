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
     * Affichage de la fiche d'un utilisateur
     * 
     * @param integer $id ID de l'utilisateur
     */
    public function showAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        return $this->render('HopitalNumeriqueUserBundle:User:show.html.twig', array(
            'user' => $user,
            'questionnaireExpert' => HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager::_getQuestionnaireId('expert'),
            'questionnaireAmbassadeur' => HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur'),
            'options' => $this->_gestionAffichageOnglet($user)
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
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
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
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );

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
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );

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
            
            //Vérification de la présence rôle
            $formRoles = $form->get("roles")->getData();
            $formRole  =  $formRoles != null ? $formRoles[0] : null;
            //$formRole  = array_shift( $formRoles );   BUG : en mode ajout WARNING         
            
            if(is_null($formRole))
            {
                $message = 'Veuillez sélectionner un groupe associé.';
                $this->get('session')->getFlashBag()->add('danger', $message);
            
                return $this->render( $view , array(
                        'form' => $form->createView(),
                        'user' => $user
                ));
            }

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($user->getId()) ? true : false;

                //Generate password for new users
                if( $new ) {
                    $passwordTool = new \Nodevo\ToolsBundle\Tools\Password();
                    $mdp = $passwordTool->generate(3,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                    $mdp .= $passwordTool->generate(3,'abcdefghijklmnopqrstuvwyyz');
                    $mdp .= $passwordTool->generate(2,'1234567890');
                    $mdp = str_shuffle($mdp);
                    $user->setPlainPassword( $mdp );

                    $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserFromAdminMail($user);
                    $this->get('mailer')->send($mail);
                }

                //security for user's roles
                $roles = $user->getRoles();
                $role  = array_shift( $roles );
                $user->setRoles( array($role) );                
                
                //Cas particulier: 1 utilisateur par groupe ARS-CMSI par région 
                $roleArscmsi     = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_ARS_CMSI_4')); 
                $roleAmbassadeur = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_AMBASSADEUR_7'));               
                $usersByRole     = $roleArscmsi->getUsers();                
                
                //Dans le cas où la région est à nulle il n'y a pas besoin de vérifier si il existe déjà un ARS pour cette région.
                if(null != $user->getRegion())
                {
                    //Pour chaque utilisateur ayant ce rôle, on vérifie qu'il n'a pas non plus la même région sinon on return et pas de sauvegarde
                    foreach ($usersByRole as $key => $userByRole)
                    {
                        if($userByRole->getRegion()->getId() == $user->getRegion()->getId()
                        && $roleArscmsi->getId() == $role->getId()
                        && $user->getId() != $userByRole->getId())
                        {
                            $message = 'Il existe déjà un utilisateur associé au groupe ARS-CMSI pour cette région.';
                            $this->get('session')->getFlashBag()->add('danger', $message);
                    
                            return $this->render( $view , array(
                                    'form' => $form->createView(),
                                    'user' => $user
                            ));
                        }
                    }
                }
                else
                {
                    //Cas particuliers : La région est obligatoire pour les roles ARS-CMSI et Ambassadeur
                    if($role->getId() == $roleArscmsi->getId()
                       || $role->getId() == $roleAmbassadeur->getId())
                    {
                        $message = 'Il est obligatoire de choisir une région pour le rôle sélectionné.';
                        $this->get('session')->getFlashBag()->add('danger', $message);
                        
                        return $this->render( $view , array(
                                'form' => $form->createView(),
                                'user' => $user
                        ));
                    }
                }
                
                //Cas particulier : 1 utilisateur ES - Direction générale par établissement de rattachement
                $roleDirectionGenerale    = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_ES_DIRECTION_GENERALE_5'));
                $usersByRoleDirectGeneral = $roleDirectionGenerale->getUsers();
                if(null != $user->getEtablissementRattachementSante())
                {
                    //Pour chaque utilisateur ayant ce rôle, on vérifie qu'il n'a pas non plus la même région sinon on return et pas de sauvegarde
                    foreach ($usersByRoleDirectGeneral as $key => $userByRoleDirectGeneral)
                    {
                        //L'utilisateur courant possède bien un établissement de rattachement
                        if(null != $userByRoleDirectGeneral->getEtablissementRattachementSante())
                        {
                            if($userByRoleDirectGeneral->getEtablissementRattachementSante()->getId() == $user->getEtablissementRattachementSante()->getId()
                            && $roleDirectionGenerale->getId() == $role->getId()
                            && $user->getId() != $userByRoleDirectGeneral->getId())
                            {
                                $message = 'Il existe déjà un utilisateur associé au groupe Direction générale pour cet établissement.';
                                $this->get('session')->getFlashBag()->add('danger', $message);
                            
                                return $this->render( $view , array(
                                        'form' => $form->createView(),
                                        'user' => $user
                                ));
                            } 
                        }
                    }
                }
                
                //bind Référence Etat with Enable FosUserField
                if( $user->getEtat()->getId() == 3 )
                    $user->setEnabled( 1 );
                else
                    $user->setEnabled( 0 );

                //Mise à jour / création de l'utilisateur
                $this->get('fos_user.user_manager')->updateUser( $user );
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Utilisateur ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                $do = $request->request->get('do');
                return $this->redirect( ( $do == 'save-close' ? $this->generateUrl('hopital_numerique_user_homepage') : $this->generateUrl('hopital_numerique_user_edit', array( 'id' => $user->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form' => $form->createView(),
            'user' => $user,
            'options' => $this->_gestionAffichageOnglet($user)
        ));
    }
}