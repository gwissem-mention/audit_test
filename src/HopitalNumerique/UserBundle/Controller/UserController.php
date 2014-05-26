<?php
namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
            //Création d'un nouvel user
            $user = $this->get('hopitalnumerique_user.manager.user')->createEmpty();
            
            //Tableau des options à passer à la vue twig
            $options = array(
                //Récupération de l'article des conditions générales
                'conditionsGenerales' => array('conditionsGenerales' => $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => 264 )))
            );
            
            //Récupérations de la liste des catégories des conditions générales
            $tmp = $options['conditionsGenerales']['conditionsGenerales'];
            $categories = $tmp->getTypes();
            
            //Récupération de la première catégorie des conditions générales (en principe il ne devrait y en avoir qu'une)
            $options['conditionsGenerales']['categorie'] = $categories[0];
            
            return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User/Front:inscription.html.twig', $options);
        }
    
        return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
    }

    /**
     * Affichage du formulaire d'inscription
     */
    public function desinscriptionAction(Request $request)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //Création du formulaire via le service
        $form = $this->createForm('nodevo_user_desinscription', $user);
        
        $view = 'HopitalNumeriqueUserBundle:User/Front:desinscription.html.twig';
        
        // Si l'utilisateur soumet le formulaire
        if ( $form->handleRequest($request)->isValid() )
        {
            $user->setEtat( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 4 ) ) );
            $user->setEnabled( 0 );

            //Mise à jour / création de l'utilisateur
            $this->get('fos_user.user_manager')->updateUser( $user );

            $this->get('security.context')->setToken(null);
            $this->get('request')->getSession()->invalidate();

            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add('success', $user->getAppellation() . ', vous venez de vous désinscrire.');
                
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }

        return $this->render( $view , array(
            'form'        => $form->createView(),
            'user'        => $user
        ));
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

        return $this->render( $view , array(
            'form'        => $form->createView(),
            'user'        => $user,
            'options'     => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user)
        ));
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
            'departement'   => $idDepartement,
            'typeOrganisme' => $idTypeEtablissement
        );
        
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
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
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
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
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
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys, 'lock' => 0) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 3) );
        $this->get('hopitalnumerique_user.manager.user')->toogleState( $users, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Activation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (caractérisation)
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );

        $colonnes = array( 
                            'id'                                 => 'id', 
                            'nom'                                => 'Nom', 
                            'prenom'                             => 'Prénom', 
                            'username'                           => 'Identifiant (login)', 
                            'email'                              => 'Adresse e-mail',
                            'etat.libelle'                       => 'Etat',
                            'region.libelle'                     => 'Région',
                            'titre.libelle'                      => 'Titre',
                            'civilite.libelle'                   => 'Civilité',
                            'telephoneDirect'                    => 'Téléphone Direct',
                            'telephonePortable'                  => 'Téléphone Portable',
                            'departement.libelle'                => 'Département',
                            'lastLoginString'                    => 'Dernière connexion',
                            'contactAutre'                       => 'Contact Autre',
                            'role'                               => 'Roles',
                            'statutEtablissementSante.libelle'   => 'Statut Etablissement Santé',
                            'profilEtablissementSante.libelle'   => 'Profil Etablissement Santé',
                            'raisonInscriptionSante.libelle'     => 'Raison inscription Santé',
                            'raisonInscriptionStructure.libelle' => 'Raison inscription structure',
                            'autreStructureRattachementSante'    => 'Nom de votre établissement si non disponible dans la liste précédente Santé',
                            'nomStructure'                       => 'Nom structure',
                            'fonctionStructure'                  => 'Fonction structure',
                            'etablissementRattachementSante.nom' => 'Etablissement rattachement Santé',
                            'dateInscriptionString'              => 'Date d\'inscription',
                            'fonctionDansEtablissementSante'     => 'Fonction dans l\'établissement de Santé',
                            'nbVisites'                          => 'Nombre de visites',
                            'raisonDesinscription'               => 'Raison de désinscription'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $users, 'export-utilisateurs.csv', $kernelCharset );
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
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
        
        //get emails
        $list = array();
        foreach($users as $user)
            if($user->getEmail() != "")
                $list[] = $user->getEmail();

        //to
        $to = $this->get('security.context')->getToken()->getUser()->getEmail();
        
        //bcc list
        $bcc = join(',', $list);
        
        return $this->render('HopitalNumeriqueUserBundle:User:mailto.html.twig', array(
            'mailto' => 'mailto:'.$to.'?bcc='.$bcc
        ));
    }
    
    /**
     * Export CSV de la liste des utilisateurs sélectionnés (candidatures experts)
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvExpertsAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users   = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
        $results = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->buildForExport( 1, $users);
        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $results['colonnes'], $results['datas'], 'export-experts.csv', $kernelCharset );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (candidatures ambassadeurs)
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAmbassadeursAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users   = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
        $results = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->buildForExport( 2, $users);
        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $results['colonnes'], $results['datas'], 'export-ambassadeurs.csv', $kernelCharset );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (productions maitrises)
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvProductionsAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
        
        //manages colonnes
        $colonnes = array('id' => 'id_utilisateur', 'user' => 'Prénom et Nom de l\'utilisateur');

        //prepare datas
        $datas     = array();
        $nbProdMax = 0;
        foreach($users as $user)
        {
            //prepare row
            $row         = array();
            $row['id']   = $user->getId();
            $row['user'] = $user->getPrenomNom();

            $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByAmbassadeur( $user->getId() );
            $nbProd = 0;
            foreach($objets as $objet){
                $row['prod'.$nbProd] = $objet->getTitre();
                $nbProd++;
            }
            
            //update nbProdMax
            if( $nbProd > $nbProdMax)
                $nbProdMax = $nbProd;

            $datas[] = $row;
        }

        //add colonnes
        for($i = 0; $i <= $nbProdMax; $i++)
            $colonnes['prod'.$i] = '';

        //add empty values
        foreach($datas as &$data){
            foreach ($colonnes as $key => $val) {
                if( !isset($data[$key]) )
                    $data[$key] = '';
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $datas, 'export-productions.csv', $kernelCharset );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (domaines fonctionnels maitrises)
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvDomainesAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy( array('id' => $primaryKeys) );
        
        //manages colonnes
        $colonnes = array('id' => 'id_utilisateur', 'user' => 'Prénom et Nom de l\'utilisateur');

        //prepare datas
        $datas     = array();
        $nbDomaineMax = 0;
        foreach($users as $user)
        {
            //prepare row
            $row         = array();
            $row['id']   = $user->getId();
            $row['user'] = $user->getPrenomNom();

            $domaines  = $user->getDomaines();
            $nbDomaine = 0;
            foreach($domaines as $domaine){
                $row['domaine'.$nbDomaine] = $domaine->getLibelle();
                $nbDomaine++;
            }
            
            //update nbDomaineMax
            if( $nbDomaine > $nbDomaineMax)
                $nbDomaineMax = $nbDomaine;

            $datas[] = $row;
        }

        //add colonnes
        for($i = 0; $i <= $nbDomaineMax; $i++)
            $colonnes['domaine'.$i] = '';

        //add empty values
        foreach($datas as &$data){
            foreach ($colonnes as $key => $val) {
                if( !isset($data[$key]) )
                    $data[$key] = '';
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $datas, 'export-domaines.csv', $kernelCharset );
    }








    
    /**
     * Effectue le render du formulaire Utilisateur
     *
     * @param string $formName Nom du service associé au formulaire
     * @param User   $entity   Entité utilisateur
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     * @param array  $options  Tableaux d'options envoyé au formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $user, $view, $options = array() )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $user);
        
        //Si on est en FO dans informations personelles, on affiche pas le mot de passe. Il est géré dans un autre formulaire
        if($this->_informationsPersonnelles)
        {
            $form->remove('plainPassword');
            $form->remove('raisonDesinscription');
        }

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
                    
                        $this->customRenderView( $view , $form, $user , $options);
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
                
                        return $this->customRenderView( $view , $form, $user , $options);
                    }
                }
                else if ( null == $user->getRegion() )
                {
                    //Cas particuliers : La région est obligatoire pour les roles ARS-CMSI et Ambassadeur
                    if( $role->getRole() == 'ROLE_ARS_CMSI_4' || $role->getRole() == 'ROLE_AMBASSADEUR_7') {
                        $this->get('session')->getFlashBag()->add('danger', 'Il est obligatoire de choisir une région pour le groupe sélectionné.' );
                        
                        $this->customRenderView( $view , $form, $user , $options);
                    }
                }
                
                //Cas particulier : 2 utilisateur ES - Direction générale par établissement de rattachement
                if( null != $user->getEtablissementRattachementSante() && $role->getRole() == 'ROLE_ES_DIRECTION_GENERALE_5')
                {
                    $result = $this->get('hopitalnumerique_user.manager.user')->userExistForRoleDirection( $user );
                    if( ! is_null($result) ) {
                        $this->get('session')->getFlashBag()->add('danger', 'Il existe déjà un utilisateur associé au groupe Direction générale pour cet établissement.');
                    
                        $this->customRenderView( $view , $form, $user , $options);
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
                        $this->get('session')->getFlashBag()->add( 'danger' , 'Certains serveurs de messagerie peuvent bloquer la bonne réception des emails émis par la plateforme Hôpital Numérique. Merci de vérifier auprès de votre service de informatique que les adresses accompagnement-hn@anap.fr et communication@anap.fr ne sont pas considérées comme du spam et qu\'elles font bien parties des adresses autorisées sur le serveur mail de votre établissement.' ); 
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
        
        return $this->customRenderView( $view , $form, $user, $options);
    }

    /**
     * [customRenderView description]
     *
     * @param  [type] $view    [description]
     * @param  [type] $form    [description]
     * @param  [type] $user    [description]
     * @param  [type] $options [description]
     *
     * @return [type]
     */
    private function customRenderView( $view, $form, $user, $options )
    {
        return $this->render( $view , array(
            'form'        => $form->createView(),
            'user'        => $user,
            'twigOptions' => $options,
            'options'     => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user)
        ));
    }
}
