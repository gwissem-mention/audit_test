<?php

namespace HopitalNumerique\UserBundle\Controller;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Event\UserEvent;
use HopitalNumerique\UserBundle\Event\UserRoleUpdatedEvent;
use HopitalNumerique\UserBundle\UserEvents;
use Nodevo\ToolsBundle\Tools\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Controller des utilisateurs.
 */
class UserController extends Controller
{
    /**
     * Vue informations personnelles sur le front.
     *
     * @var bool
     */
    protected $informationsPersonnelles = false;

    //---- Front Office ------

    /**
     * Affichage du formulaire d'inscription.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function inscriptionAction(Request $request)
    {
        //Si il n'y a pas d'utilisateur connecté
        if (!$this->isGranted('ROLE_USER')) {
            if ($this->container->get('hopitalnumerique_account.doctrine.reference.contexte')
                ->isWantCreateUserWithContext()
            ) {
                //Création d'un nouvel user avec contexte préremplis
                $referenceIds = $this->container->get(
                    'hopitalnumerique_recherche.dependency_injection.referencement.requete_session'
                )->getReferenceIds();
                $user = $this->container->get('hopitalnumerique_account.doctrine.reference.contexte')
                    ->getNewUserWithContexte($referenceIds)
                ;
            } else {
                $user = $this->get('hopitalnumerique_user.manager.user')->createEmpty();
            }

            //Tableau des options à passer à la vue twig
            $options = [
                //Récupération de l'article des conditions générales
                'conditionsGenerales' => [
                    'conditionsGenerales' => $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(
                        ['id' => 264]
                    ),
                ],
            ];

            //Récupérations de la liste des catégories des conditions générales
            $tmp = $options['conditionsGenerales']['conditionsGenerales'];
            $categories = $tmp->getTypes();

            // Récupération de la première catégorie des conditions générales
            // (en principe il ne devrait y en avoir qu'une)
            $options['conditionsGenerales']['categorie'] = $categories[0];

            $request->request->add(['do' => 'inscription']);

            return $this->renderForm(
                'nodevo_user_registration',
                $user,
                'HopitalNumeriqueUserBundle:User/Front:inscription.html.twig',
                $options
            );
        }

        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }

    /**
     * Affichage du formulaire d'inscription.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function desinscriptionAction(Request $request)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        //Création du formulaire via le service
        $form = $this->createForm('nodevo_user_desinscription', $user);

        $view = 'HopitalNumeriqueUserBundle:User/Front:desinscription.html.twig';

        // Si l'utilisateur soumet le formulaire
        if ($form->handleRequest($request)->isValid()) {
            $user->setEtat($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 4]));
            $user->setEnabled(0);

            //Mise à jour / création de l'utilisateur
            $this->get('fos_user.user_manager')->updateUser($user);

            $this->get('security.context')->setToken(null);
            $this->get('request')->getSession()->invalidate();

            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add(
                'success',
                $user->getAppellation() . ', vous venez de vous désinscrire.'
            );

            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Affichage du formulaire d'utilisateur.
     */
    public function informationsPersonnellesAction()
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        $this->informationsPersonnelles = true;

        return $this->renderForm(
            'nodevo_user_user',
            $user,
            'HopitalNumeriqueUserBundle:User/Front:informations_personnelles.html.twig'
        );
    }

    /**
     * Affichage du formulaire de modification du mot de passe.
     *
     * @internal param int $id Identifiant de l'utilisateur
     *
     * @return RedirectResponse|Response
     */
    public function motDePasseAction()
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        //Création du formulaire via le service
        $form = $this->createForm('nodevo_user_motdepasse', $user);

        $view = 'HopitalNumeriqueUserBundle:User/Front:motdepasse.html.twig';

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);

                //Vérifie si le mot de passe entré dans le formulaire correspondant au mot de passe de l'utilisateur
                if ($encoder->isPasswordValid(
                    $user->getPassword(),
                    $form->get('oldPassword')->getData(),
                    $user->getSalt()
                )) {
                    //Mise à jour / création de l'utilisateur
                    $this->get('fos_user.user_manager')->updateUser($user);

                    // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                    $this->get('session')->getFlashBag()->add('success', 'Mot de passe mis à jour.');

                    return $this->redirect($this->generateUrl('hopital_numerique_user_informations_personnelles'));
                } else {
                    // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                    $this->get('session')->getFlashBag()->add('danger', 'L\'ancien mot de passe saisi est incorrect.');

                    return $this->redirect($this->generateUrl('hopital_numerique_user_motdepasse'));
                }
            }
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'user' => $user,
            'options' => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
        ]);
    }

    /**
     * Changement de l'état de notification des mises à jour des publications.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function toggleNotificationRequeteAction(Request $request)
    {
        $user = $this->getUser();

        $notifier = $request->request->get('notifier') == 'true';

        $user->setNotficationRequete($notifier);
        $this->get('fos_user.user_manager')->updateUser($user);

        return new Response('{"success":true"}', 200);
    }

    public function getUserFromEmailAction(Request $request)
    {
        $email = $request->request->get('email');

        $userRecherche = $this->get('hopitalnumerique_user.manager.user')->findOneBy(['email' => $email]);

        $response = json_encode([
            'success' => true,
            'datas' => [
                'user' => !is_null($userRecherche) ? $userRecherche->getId() : 'ko',
            ],
        ]);

        return new Response($response, 200);
    }

    //---- Back Office ------

    /**
     * Affichage des utilisateurs.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_user.grid.user');

        return $grid->render('HopitalNumeriqueUserBundle:User:index.html.twig');
    }

    /**
     * Affichage des utilisateurs.
     *
     * @param string $filtre
     * @param string|null $domain
     *
     * @return Response
     */
    public function indexFiltreAction($filtre, $domain = null)
    {
        $grid = $this->get('hopitalnumerique_user.grid.user');
        $grid->setId($filtre);

        if (!is_null($filtre)) {
            $grid->setDefaultFiltreFromController($filtre, $domain);
        }

        return $grid->render('HopitalNumeriqueUserBundle:User:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout d'utilisateur.
     */
    public function addAction()
    {
        /** @var User $user */
        $user = $this->get('hopitalnumerique_user.manager.user')->createEmpty();

        $role = $this->get('nodevo_role.manager.role')->findOneBy(['role' => 'ROLE_ENREGISTRE_9']);

        $user->setRoles([$role]);

        return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:edit.html.twig');
    }

    /**
     * Affichage du formulaire d'utilisateur.
     *
     * @param int $id Identifiant de l'utilisateur
     *
     * @return Form|RedirectResponse
     */
    public function editAction($id)
    {
        /** @var User $user */
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy(['id' => $id]);

        $options['isAllowedToSwitch'] = false;

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATEUR_1')) {
            $options['isAllowedToSwitch'] = true;
        }

        return $this->renderForm('nodevo_user_user', $user, 'HopitalNumeriqueUserBundle:User:edit.html.twig', $options);
    }

    /**
     * Affichage de la fiche d'un utilisateur.
     *
     * @param int $id ID de l'utilisateur
     *
     * @return Response
     */
    public function showAction($id)
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy(['id' => $id]);
        $roles = $this->get('nodevo_role.manager.role')->findIn($user->getRoles());

        return $this->render('HopitalNumeriqueUserBundle:User:show.html.twig', [
            'user' => $user,
            'questionnaireExpert' => $this->get('hopitalnumerique_questionnaire.manager.questionnaire')
                ->getQuestionnaireId('expert'),
            'questionnaireAmbassadeur' => $this->get('hopitalnumerique_questionnaire.manager.questionnaire')
                ->getQuestionnaireId('ambassadeur'),
            'options' => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'roles' => $roles,
        ]);
    }

    /**
     * @param User $user
     *
     * @return Response
     */
    public function historiqueAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var LogEntryRepository $repo */
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $repo->getLogEntries($user);

        $logsSynthesis = $repo->findBy([
            'username' => $user->getUsername(),
            'objectClass' => 'HopitalNumerique\AutodiagBundle\Entity\Synthesis',
        ]);

        $logsModule = $repo->findBy([
            'username' => $user->getUsername(),
            'objectClass' => 'HopitalNumerique\ModuleBundle\Entity\Module',
        ]);

        $logsIntervention = $repo->findBy([
            'username' => $user->getUsername(),
            'objectClass' => 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande',
        ]);

        $logsFacturation = $repo->findBy([
            'username' => $user->getUsername(),
            'objectClass' => 'HopitalNumerique\PaiementBundle\Entity\Facture',
            'action' => 'create',
        ]);

        $logsQuestionnaire = $repo->findBy([
            'username' => $user->getUsername(),
            'objectClass' => 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire',
        ]);

        return $this->render('HopitalNumeriqueUserBundle:User:historique.html.twig', [
            'logs' => $logs,
            'logsSynthesis' => $logsSynthesis,
            'logsModule' => $logsModule,
            'logsIntervention' => $logsIntervention,
            'logsFacturation' => $logsFacturation,
            'logsQuestionnaire' => $logsQuestionnaire,
        ]);
    }

    /**
     * Suppression d'un utilisateur.
     *
     * @param int $id ID de l'utilisateur
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy(['id' => $id]);

        //L'utilisateur super admin est par défaut à l'id 1, il ne peut jamais être supprimé
        if (!$user->getLock()) {
            //Suppression de l'utilisateur
            $this->get('hopitalnumerique_user.manager.user')->delete($user);
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
        } else {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Vous ne pouvez pas supprimer un utilisateur vérouillé.'
            );
        }

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl('hopital_numerique_user_homepage') . '"}',
            200
        );
    }

    /**
     * Returns the list of counties in the selected region
     *
     * @return Response
     */
    public function ajaxLoadCountiesAction()
    {
        $id = $this->get('request')->request->get('id');
        $counties = [];

        if ('' != $id) {
            $counties = $this->get('hopitalnumerique_reference.manager.reference')->findByParent(
                $this->get('hopitalnumerique_reference.manager.reference')->findOneById($id)
            );
        }

        return $this->render('HopitalNumeriqueUserBundle:User:counties.html.twig', [
            'counties' => $counties,
        ]);
    }

    /**
     * @return Response
     */
    public function ajaxLoadOrganizationsAction()
    {
        $county = $this->get('request')->request->get('county');
        $organizationType = $this->get('request')->request->get('organizationType');

        $organizations = [];
        if ('' != $county && '' != $organizationType) {
            $organizations = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findBy([
                'departement' => $county,
                'typeOrganisme' => $organizationType,
            ]);
        }

        return $this->render('HopitalNumeriqueUserBundle:User:organizations.html.twig', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Génère la liste des établissement en fonction de l'id du département.
     */
    public function ajaxEditEtablissementsAction()
    {
        $idDepartement = $this->get('request')->request->get('idDepartement');
        $idTypeEtablissement = $this->get('request')->request->get('idTypeEtablissement');
        //Par défaut le département est obligatoire
        $where = [
            'departement' => $idDepartement,
            'typeOrganisme' => $idTypeEtablissement,
        ];

        $etablissements = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findBy($where);

        return $this->render('HopitalNumeriqueUserBundle:User:etablissements.html.twig', [
            'etablissements' => $etablissements,
        ]);
    }

    /**
     * Suppression de masse des users.
     *
     * @param $primaryKeys
     * @param $allPrimaryKeys
     *
     * @return RedirectResponse
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //check connected user ACL
        $user = $this->getUser();

        if ($this->get('nodevo_acl.manager.acl')->checkAuthorization(
            $this->generateUrl('hopital_numerique_user_delete', ['id' => 1]),
            $user
        ) == -1) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Vous n\'avez pas les droits suffisants pour supprimer des utilisateurs.'
            );

            return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
        }

        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys, 'lock' => 0]);
        $this->get('hopitalnumerique_user.manager.user')->delete($users);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
    }

    /**
     * Désactivation de masse des users.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return RedirectResponse
     */
    public function desactiverMassAction($primaryKeys, $allPrimaryKeys)
    {
        //check connected user ACL
        $user = $this->getUser();

        if ($this->get('nodevo_acl.manager.acl')->checkAuthorization(
            $this->generateUrl('hopital_numerique_user_delete', ['id' => 1]),
            $user
        ) == -1) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Vous n\'avez pas les droits suffisants pour désactiver des utilisateurs.'
            );

            return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
        }

        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys, 'lock' => 0]);

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 4]);
        $this->get('hopitalnumerique_user.manager.user')->toogleState($users, $ref);

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Désactivation effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
    }

    /**
     * Activation de masse des users.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return RedirectResponse
     */
    public function activerMassAction($primaryKeys, $allPrimaryKeys)
    {
        //check connected user ACL
        $user = $this->getUser();

        if ($this->get('nodevo_acl.manager.acl')->checkAuthorization(
            $this->generateUrl('hopital_numerique_user_delete', ['id' => 1]),
            $user
        ) == -1) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Vous n\'avez pas les droits suffisants pour activer des utilisateurs.'
            );

            return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
        }

        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys, 'lock' => 0]);

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 3]);
        $this->get('hopitalnumerique_user.manager.user')->toogleState($users, $ref);

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Activation effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (caractérisation).
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvAction($primaryKeys, $allPrimaryKeys)
    {
        ini_set('memory_limit', '2048M');

        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);

        $colonnes = [
            'id' => 'id',
            'lastname' => 'Nom',
            'firstname' => 'Prénom',
            'username' => 'Identifiant (login)',
            'email' => 'Adresse e-mail',
            'pseudonym' => 'Pseudonyme pour le forum',
            'etat.libelle' => 'Etat',
            'phoneNumber' => 'Téléphone Direct',
            'cellPhoneNumber' => 'Téléphone Portable',
            'otherContact' => 'Autres contacts',
            'profileType.libelle' => 'Profil Etablissement Santé',
            'jobType.libelle' => 'Rôle',
            'jobLabel' => 'Libelle rôle',
            'organization.nom' => 'Structure',
            'activitiesString' => 'Activités',
            'organizationLabel' => 'Nom de votre structure si non disponible dans la liste précédente',
            'organizationType.libelle' => 'Type de structure',
            'region.libelle' => 'Région',
            'county.libelle' => 'Département',
            'presentation' => 'Présentation',
            'computerSkillsString' => 'Logiciels maîtrisés',
            'lastLoginString' => 'Dernière connexion',
            'role' => 'Roles',
            'registrationDateString' => 'Date d\'inscription',
            'visitCount' => 'Nombre de visites',
            'raisonDesinscription' => 'Raison de désinscription',
            'remarque' => 'Remarque pour la gestion',
            'domainesString' => 'Domaine(s) concerné(s)',
            'ipLastConnection' => 'Dernière ip de connexion',
            'UpToDateToString' => 'À jour',
            'activityNewsletterEnabled' => 'Newsletter des activités de l\'ANAP',
            'inscritCommunautePratiqueString' => 'Membre de la CDP',
        ];

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $colonnes,
            $users,
            'export-utilisateurs.csv',
            $kernelCharset
        );
    }

    /**
     * Export CSV des ambassadeurs et leurs inscriptions aux sessions.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return RedirectResponse
     */
    public function sessionsMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);
        $modules = $this->get('hopitalnumerique_module.manager.module')->findAll();
        $results = $this->get('hopitalnumerique_module.manager.inscription')->buildForExport(
            $modules,
            $users,
            $primaryKeys
        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $results['colonnes'],
            $results['datas'],
            'ambassadeurs_sessions.csv',
            $kernelCharset
        );
    }

    /**
     * Envoyer un mail aux utilisateurs.
     *
     * @param $primaryKeys
     * @param $allPrimaryKeys
     *
     * @return Response
     */
    public function envoyerMailMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);

        //get emails
        $list = [];
        foreach ($users as $user) {
            if ($user->getEmail() != '') {
                $list[] = $user->getEmail();
            }
        }

        //to
        $to = $this->getUser()->getEmail();

        //bcc list
        $bcc = join(',', $list);

        return $this->render('HopitalNumeriqueUserBundle:User:mailto.html.twig', [
            'mailto' => 'mailto:' . $to . '?bcc=' . $bcc,
            'list'   => $list,
        ]);
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (candidatures experts).
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvExpertsAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);
        $results = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->buildForExport(1, $users);
        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $results['colonnes'],
            $results['datas'],
            'export-experts.csv',
            $kernelCharset
        );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (candidatures ambassadeurs).
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvAmbassadeursAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);
        $results = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->buildForExport(2, $users);
        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $results['colonnes'],
            $results['datas'],
            'export-ambassadeurs.csv',
            $kernelCharset
        );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (productions maitrises).
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvProductionsAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);

        //manages colonnes
        $colonnes = ['id' => 'id_utilisateur', 'user' => 'Prénom et Nom de l\'utilisateur'];

        //prepare datas
        $datas = [];
        $nbProdMax = 0;

        /** @var User $user */
        foreach ($users as $user) {
            //prepare row
            $row = [];
            $row['id'] = $user->getId();
            $row['user'] = $user->getPrenomNom();

            $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByAmbassadeur($user->getId());
            $nbProd = 0;

            /** @var Objet $objet */
            foreach ($objets as $objet) {
                $row['prod' . $nbProd] = $objet->getTitre();
                ++$nbProd;
            }

            //update nbProdMax
            if ($nbProd > $nbProdMax) {
                $nbProdMax = $nbProd;
            }

            $datas[] = $row;
        }

        //add colonnes
        for ($i = 0; $i <= $nbProdMax; ++$i) {
            $colonnes['prod' . $i] = '';
        }

        //add empty values
        foreach ($datas as &$data) {
            foreach ($colonnes as $key => $val) {
                if (!isset($data[$key])) {
                    $data[$key] = '';
                }
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $colonnes,
            $datas,
            'export-productions.csv',
            $kernelCharset
        );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (domaines fonctionnels maitrises).
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvDomainesAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);

        //manages colonnes
        $colonnes = ['id' => 'id_utilisateur', 'user' => 'Prénom et Nom de l\'utilisateur'];

        //prepare datas
        $datas = [];
        $nbDomaineMax = 0;

        /** @var User $user */
        foreach ($users as $user) {
            //prepare row
            $row = [];
            $row['id'] = $user->getId();
            $row['user'] = $user->getPrenomNom();

            $connaissances = $user->getConnaissancesAmbassadeurs();
            $nbDomaine = 0;

            foreach ($connaissances as $connaissance) {
                if (!is_null($connaissance->getConnaissance())) {
                    $row['domaine' . $nbDomaine] = $connaissance->getDomaine()->getLibelle();
                    $row['domaine' . $nbDomaine . 'connaissance'] = $connaissance->getConnaissance()->getLibelle();
                    ++$nbDomaine;
                }
            }

            //update nbDomaineMax
            if ($nbDomaine > $nbDomaineMax) {
                $nbDomaineMax = $nbDomaine;
            }

            $datas[] = $row;
        }

        //add colonnes
        for ($i = 0; $i <= $nbDomaineMax; ++$i) {
            $colonnes['domaine' . $i] = '';
            $colonnes['domaine' . $i . 'connaissance'] = '';
        }

        //add empty values
        foreach ($datas as &$data) {
            foreach ($colonnes as $key => $val) {
                if (!isset($data[$key])) {
                    $data[$key] = '';
                }
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $colonnes,
            $datas,
            'export-domaines.csv',
            $kernelCharset
        );
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (domaines fonctionnels maitrises).
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvConnaissancesSIAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.user')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $users = $this->get('hopitalnumerique_user.manager.user')->findBy(['id' => $primaryKeys]);

        //manages colonnes
        $colonnes = ['id' => 'id_utilisateur', 'user' => 'Prénom et Nom de l\'utilisateur'];

        //prepare datas
        $datas = [];
        $nbDomaineMax = 0;

        /** @var User $user */
        foreach ($users as $user) {
            //prepare row
            $row = [];
            $row['id'] = $user->getId();
            $row['user'] = $user->getPrenomNom();

            $connaissances = $user->getConnaissancesAmbassadeursSI();
            $nbDomaine = 0;
            foreach ($connaissances as $connaissance) {
                if (!is_null($connaissance->getConnaissance())) {
                    $row['domaine' . $nbDomaine] = $connaissance->getDomaine()->getLibelle();
                    $row['domaine' . $nbDomaine . 'connaissance'] = $connaissance->getConnaissance()->getLibelle();
                    ++$nbDomaine;
                }
            }

            //update nbDomaineMax
            if ($nbDomaine > $nbDomaineMax) {
                $nbDomaineMax = $nbDomaine;
            }

            $datas[] = $row;
        }

        //add colonnes
        for ($i = 0; $i <= $nbDomaineMax; ++$i) {
            $colonnes['domaine' . $i] = '';
            $colonnes['domaine' . $i . 'connaissance'] = '';
        }

        //add empty values
        foreach ($datas as &$data) {
            foreach ($colonnes as $key => $val) {
                if (!isset($data[$key])) {
                    $data[$key] = '';
                }
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $colonnes,
            $datas,
            'export-domaines.csv',
            $kernelCharset
        );
    }

    /**
     * Effectue le render du formulaire Utilisateur.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param User   $user     Entité utilisateur
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     * @param array  $options  Tableaux d'options envoyé au formulaire
     *
     * @return RedirectResponse|Response
     */
    private function renderForm($formName, $user, $view, $options = [])
    {
        $oldUser = clone $user;
        //Création du formulaire via le service
        $form = $this->createForm($formName, $user);

        //Si on est en FO dans informations personelles, on affiche pas le mot de passe.
        //Il est géré dans un autre formulaire
        if ($this->informationsPersonnelles) {
            $form->remove('plainPassword');
            $form->remove('remarque');
            $form->remove('biographie');
            $form->remove('domaines');
            $form->remove('raisonDesinscription');
            $form->remove('roles');
        }

        //GME : ticket 3088 = un admin de domaine ne peut modifier son propre role ni ses domaines
        if ($this->isGranted('ROLE_ADMINISTRATEUR_DE_DOMAINE_106')
            && ($this->getUser()->getId() === $user->getId())
        ) {
            $form->remove('roles');
            $form->remove('domaines');
        }

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            $this->get('event_dispatcher')->dispatch(UserEvents::USER_PRE_UPDATE, new UserEvent(clone $user));

            // On bind les données du form
            $form->handleRequest($request);

            //Vérification d'un utilisateur connecté
            if ($this->isGranted('ROLE_USER')) {
                //Si un utilisateur est connecté mais qu'on est en FO : informations personnelles
                if (!$this->informationsPersonnelles) {
                    //--Backoffice--
                    //Vérification de la présence rôle
                    $role = $form->get('roles')->getData();
                    if (is_null($role)) {
                        $this->get('session')->getFlashBag()->add('danger', 'Veuillez sélectionner un groupe associé.');

                        $this->customRenderView($view, $form, $user, $options);
                    }
                }
            } else {
                //--FO-- inscription
                //Set de l'état
                $idEtatActif = intval(
                    $this->get('hopitalnumerique_user.options.user')->getOptionsByLabel('idEtatActif')
                );
                $user->setEtat(
                    $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => $idEtatActif])
                );
                $user->setDomaines([
                    $this->get('hopitalnumerique_domaine.manager.domaine')->findOneById(
                        $request->getSession()->get('domaineId')
                    ),
                ]);
            }

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($user->getId());

                $roleHasChanged = false;

                //Generate password for new users
                if ($new) {
                    $user->setRegistrationDate(new \DateTime());

                    //Différence entre le FO et BO : vérification qu'il y a un utilisateur connecté
                    if ($this->isGranted('ROLE_USER')) {
                        //--BO--
                        $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserFromAdminMail($user, []);
                        $this->get('mailer')->send($mail);
                    } else {
                        $user->setEnabled(0);
                        $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
                        //--FO--
                        $mail = $this->get('nodevo_mail.manager.mail')->sendAjoutUserMail($user, []);
                        $this->get('mailer')->send($mail);
                    }
                } else {
                    if ($form->has('roles') && $oldUser->getRoles()[0] != $form->get('roles')->getData()->getRole()) {
                        $roleHasChanged = true;
                        $action = 'update';
                        $class = 'HopitalNumerique\UserBundle\Entity\User';

                        $this->container->get('hopitalnumerique_core.log')->Logger(
                            $action,
                            $user,
                            $form->get('roles')->getData()->getName(),
                            $class,
                            $this->getUser()
                        )
                        ;
                    }
                }

                //Vérification d'un utilisateur connecté
                if ($this->isGranted('ROLE_USER')) {
                    if ($this->informationsPersonnelles) {
                        //--Frontoffice-- Informations personnelles
                        //Reforce le role de l'utilisateur pour éviter qu'il soit modifié
                        $connectedUser = $this->getUser();
                        $roleUserConnectedLabel = $this->get('nodevo_role.manager.role')->getUserRole($connectedUser);

                        //Test etab user
                        if ($roleUserConnectedLabel == 'ROLE_ENREGISTRE_9' || $roleUserConnectedLabel == 'ROLE_ES_8') {
                            if (!is_null($user->getOrganization())) {
                                $role = $this->get('nodevo_role.manager.role')->findOneBy(['role' => 'ROLE_ES_8']);
                            } else {
                                $role = $this->get('nodevo_role.manager.role')->findOneBy(
                                    ['role' => 'ROLE_ENREGISTRE_9']
                                );
                            }
                        } else {
                            $role = $this->get('nodevo_role.manager.role')->findOneBy(
                                ['role' => $roleUserConnectedLabel]
                            );
                        }

                        $user->setRoles([$role]);

                        //Reforce l'username
                        $user->setUsername($user->getUsername());
                    } else {
                        //--BO--
                        //set Role for User : not mapped field
                        $user->setRoles([$role->getRole()]);
                        if ($role->getRole() == 'ROLE_AMBASSADEUR_7') {
                            $user->setAlreadyBeAmbassadeur(true);
                        } elseif ($role->getRole() == 'ROLE_EXPERT_6') {
                            $user->setAlreadyBeExpert(true);
                        }
                        $this->get('event_dispatcher')->dispatch(UserEvents::USER_UPDATED, new UserEvent($user));
                    }
                } else {
                    //--FO-- Inscription
                    //Set du role "Enregistré" par défaut pour les utilisateurs
                    if (!is_null($user->getOrganization())) {
                        $role = $this->get('nodevo_role.manager.role')->findOneBy(['role' => 'ROLE_ES_8']);
                    } else {
                        $role = $this->get('nodevo_role.manager.role')->findOneBy(['role' => 'ROLE_ENREGISTRE_9']);
                    }
                    $user->setRoles([$role->getRole()]);
                }

                if (null == $user->getRegion()) {
                    //Cas particuliers : La région est obligatoire pour les roles ARS-CMSI et Ambassadeur
                    if ($role->getRole() == 'ROLE_ARS_CMSI_4' || $role->getRole() == 'ROLE_AMBASSADEUR_7') {
                        $this->get('session')->getFlashBag()->add(
                            'danger',
                            'Il est obligatoire de choisir une région pour le groupe sélectionné.'
                        );

                        $this->customRenderView($view, $form, $user, $options);
                    }
                }

                //Cas particulier : 2 utilisateur ES - Direction générale par structure de rattachement
                if (null != $user->getOrganization()
                    && $role->getRole()
                       == 'ROLE_ES_DIRECTION_GENERALE_5'
                ) {
                    $result = $this->get('hopitalnumerique_user.manager.user')->userExistForRoleDirection($user);
                    if (!is_null($result)) {
                        $this->get('session')->getFlashBag()->add(
                            'danger',
                            'Il existe déjà un utilisateur associé au groupe Direction générale pour cet établissement.'
                        );

                        $this->customRenderView($view, $form, $user, $options);
                    }
                }

                //bind Référence Etat with Enable FosUserField
                if (intval($this->get('hopitalnumerique_user.options.user')->getOptionsByLabel('idEtatActif'))
                    === $user->getEtat()->getId() && $this->isGranted('ROLE_USER')
                ) {
                    $user->setEnabled(1);
                } else {
                    $user->setEnabled(0);
                }

                $user->setDateLastUpdate(new \DateTime());

                //Mise à jour / création de l'utilisateur
                $this->get('fos_user.user_manager')->updateUser($user);
                if ($new
                    && $this->container->get(
                        'hopitalnumerique_recherche.dependency_injection.referencement.requete_session'
                    )->isWantToSaveRequete()
                ) {
                    $this->container->get(
                        'hopitalnumerique_recherche.dependency_injection.referencement.requete_session'
                    )->saveAsNewRequete($user);
                }

                if ($new || $roleHasChanged) {
                    /**
                     * Fire 'USER_ROLE_UPDATED' event
                     */
                    $oldRole = $new ? '' : $oldUser->getRoles()[0];
                    $event = new UserRoleUpdatedEvent($user, $oldRole);
                    $this->get('event_dispatcher')->dispatch(UserEvents::USER_ROLE_UPDATED, $event);
                }

                if ($user->isInscritCommunautePratique() && ($new || !$oldUser->isInscritCommunautePratique())) {
                    /**
                     * Fire 'ENROLL_USER' event
                     */
                    $event = new EnrolmentEvent($user);
                    $this->get('event_dispatcher')->dispatch(Events::ENROLL_USER, $event);
                }

                $do = $request->request->get('do');

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                if ($do == 'inscription') {
                    //<-- Connexion automatique
                    $token = new UsernamePasswordToken($user, null, 'frontoffice_connecte', $user->getRoles());
                    $this->get('security.context')->setToken($token);
                    $this->get('event_dispatcher')->dispatch(
                        'security.interactive_login',
                        new InteractiveLoginEvent($request, $token)
                    );
                    //-->
                } else {
                    $this->get('session')->getFlashBag()->add(
                        ($new ? 'success' : 'info'),
                        'Utilisateur ' . $user->getUsername() . ($new ? ' ajouté.' : ' mis à jour.')
                    );
                }

                switch ($do) {
                    case 'inscription':
                        $this->get('session')->getFlashBag()->add('success', strip_tags($this->get('translator')->trans('register.mail_warning')));
                        $urlParameter = $request->getSession()->get('urlToRedirect');
                        $request->getSession()->remove('urlToRedirect');

                        return $this->redirect(
                            is_null($urlParameter) || $urlParameter == '' ? $this->generateUrl(
                                'hopital_numerique_homepage'
                            ) : $urlParameter
                        );
                        break;
                    case 'information-personnelles':
                        return $this->redirect($this->generateUrl('hopital_numerique_user_informations_personnelles'));
                        break;
                    case 'save-close':
                        return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
                        break;
                    default:
                        return $this->redirect(
                            $this->generateUrl('hopital_numerique_user_edit', ['id' => $user->getId()])
                        );
                        break;
                }
            }
        }

        return $this->customRenderView($view, $form, $user, $options);
    }

    /**
     * @param $view
     * @param $form
     * @param $user
     * @param $options
     *
     * @return Response
     */
    private function customRenderView($view, $form, $user, $options = [])
    {
        return $this->render($view, [
            'form' => $form->createView(),
            'user' => $user,
            'twigOptions' => $options,
            'options' => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'domainesCommunsWithUser' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntityDomainesCommunsWithUser($user, $this->getUser()),
        ]);
    }
}
