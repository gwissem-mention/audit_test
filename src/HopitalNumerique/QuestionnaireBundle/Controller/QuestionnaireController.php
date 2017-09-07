<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire as HopiQuestionnaire;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Controller des Questionnaire.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class QuestionnaireController extends Controller
{
    const REDIRECT_REFERER_SESSION_KEY = 'questionnaire.redirect.referer';

    /**
     * Tableau de la route de redirection sous la forme :
     * array(
     *   'sauvegarde' => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
     *   'quit'       => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
     *  ).
     *
     * @var array
     */
    private $routeRedirection = [];

    /**
     * Theme du formulaire utilisé.
     *
     * @var string
     */
    private $themeQuestionnaire;

    /**
     * Envoie d'un mail de confirmation.
     *
     * @var bool
     */
    private $envoieDeMail;

    /* Gestionnaire des questionnaires */

    /**
     * Liste tous les questionnaires (avec occurrences) de l'utilisateur connecté.
     *
     * @return Response
     */
    public function listAction()
    {
        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')->findOneById(
            $this->container->get('session')->get('domaineId')
        );
        $questionnairesWithDates = $this->container->get('hopitalnumerique_questionnaire.manager.questionnaire')
            ->findByUserAndDomaineWithDates($this->getUser(), $domaine, false)
        ;

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:list.html.twig', [
            'questionnairesWithDates' => $questionnairesWithDates,
        ]);
    }

    /**
     * Affiche la liste des questionnaires.
     *
     * @return Response
     */
    public function indexQuestionnaireAction()
    {
        //Récupérations de l'ensemble des questionnaires pour l'export
        $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findBy(
            ['lock' => false],
            ['nom' => 'ASC']
        );

        //Génération du grid
        $grid = $this->get('hopitalnumerique_questionnaire.grid.questionnaire');

        return $grid->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/index.html.twig', [
            'questionnaires' => $questionnaires,
        ]);
    }

    /**
     * Editer le questionnaire.
     *
     * @param HopiQuestionnaire $questionnaire
     *
     * @return RedirectResponse|Response
     */
    public function editQuestionnaireAction(HopiQuestionnaire $questionnaire)
    {
        return $this->renderGestionForm('hopitalnumerique_questionnaire_gestion_questionnaire', $questionnaire);
    }

    /**
     * Affiche le formulaire d'ajout de Module.
     *
     * @return RedirectResponse|Response
     */
    public function addQuestionnaireAction()
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->createEmpty();

        return $this->editQuestionnaireAction($questionnaire);
    }

    /**
     * Suppresion d'un Module.
     *
     * @param int $id Id de Module.
     * METHOD = POST|DELETE
     *
     * @return Response
     */
    public function deleteQuestionnaireAction($id)
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy(['id' => $id]);

        if (count($questionnaire->getOutils()) > 0) {
            $this->addFlash(
                'danger',
                'Suppression impossible car le questionnaire est utilisé par un ou plusieurs autodiags.'
            );

            return new Response(
                '{"success":false, "url" : "' . $this->generateUrl('hopitalnumerique_questionnaire_index') . '"}',
                200
            );
        }

        //Suppression de l'entitée
        $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->delete($questionnaire);

        $this->addFlash('info', 'Suppression effectuée avec succès.');

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl('hopitalnumerique_questionnaire_index') . '"}',
            200
        );
    }

    /**
     * Suppression de masse des questionnaires.
     *
     * @param $primaryKeys
     * @param $allPrimaryKeys
     *
     * @return RedirectResponse
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findBy(
            ['id' => $primaryKeys]
        );

        /** @var Questionnaire $questionnaire */
        foreach ($questionnaires as $questionnaire) {
            if (count($questionnaire->getOutils()) > 0) {
                $this->addFlash(
                    'danger',
                    'Suppression impossible car le questionnaire "'
                    . $questionnaire->getNom()
                    . '" est utilisé par un ou plusieurs autodiags.'
                );

                return $this->redirect($this->generateUrl('hopitalnumerique_questionnaire_index'));
            }
        }

        $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->delete($questionnaires);

        $this->addFlash('info', 'Suppression effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopitalnumerique_questionnaire_index'));
    }

    /**
     * Affichage du formulaire d'utilisateur.
     *
     * @param Request           $request
     * @param HopiQuestionnaire $questionnaire
     * @param bool              $redirectReferer
     *
     * @return Response
     */
    public function editFrontGestionnaireAction(
        Request $request,
        HopiQuestionnaire $questionnaire,
        $redirectReferer = false
    ) {
        if ((bool) $redirectReferer === true && !$this->get('session')->has(self::REDIRECT_REFERER_SESSION_KEY)) {
            $referer = $request->headers->get('referer');
            $this->get('session')->set(self::REDIRECT_REFERER_SESSION_KEY, $referer);
        }

        return $this->editFrontGestionnaireOccurrenceAction($questionnaire);
    }

    /**
     * Même action que editFrontGestionnaireAction() avec la paramètre Occurrence indiqué.
     *
     * @param Questionnaire $questionnaire Questionnaire
     * @param Occurrence    $occurrence    Occurrence
     *
     * @return Response
     */
    public function editFrontGestionnaireOccurrenceAction(
        HopiQuestionnaire $questionnaire,
        Occurrence $occurrence = null
    ) {
        $currentDomain = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        if (!$questionnaire->getDomaines()->contains($currentDomain)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();

        $this->envoieDeMail = false;

        if (null !== $occurrence) {
            $route = 'hopitalnumerique_questionnaire_edit_front_gestionnaire_occurrence';
            $routeParameters = ['questionnaire' => $questionnaire->getId(), 'occurrence' => $occurrence->getId()];
        } else {
            $route = 'hopitalnumerique_questionnaire_edit_front_gestionnaire';
            $routeParameters = ['id' => $questionnaire->getId()];
        }

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Front/index.html.twig', [
            'questionnaire' => $questionnaire,
            'occurrence' => $occurrence,
            'user' => $user,
            'optionRenderForm' => [
                'showAllQuestions' => false,
                'readOnly' => false,
                'envoieDeMail' => true,
                'themeQuestionnaire' => 'vertical',
                'routeRedirect' => json_encode([
                    'quit' => [
                        'route' => $route,
                        'arguments' => $routeParameters,
                    ],
                ]),
            ],
        ]);
    }

    /* Gestionnaire des formulaires */

    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param,
     * ajout d'une route de redirection quand tout s'est bien passé.
     *
     * @param HopiUser          $user          Utilisateur courant
     * @param HopiQuestionnaire $questionnaire Questionnaire à afficher
     * @param Occurrence        $occurrence
     * @param array             $optionRenderForm
     *
     * @return RedirectResponse|Response
     */
    public function editFrontAction(
        HopiUser $user,
        HopiQuestionnaire $questionnaire,
        Occurrence $occurrence = null,
        $optionRenderForm = []
    ) {
        $readOnly           = array_key_exists('readOnly', $optionRenderForm) ? $optionRenderForm['readOnly'] : false;
        $routeRedirection   = array_key_exists('routeRedirect', $optionRenderForm) ? $optionRenderForm['routeRedirect']
            : '';
        $themeQuestionnaire = array_key_exists('themeQuestionnaire', $optionRenderForm)
            ? $optionRenderForm['themeQuestionnaire'] : 'default';
        $this->envoieDeMail = array_key_exists('envoieDeMail', $optionRenderForm) ? $optionRenderForm['envoieDeMail']
            : true;
        $showAllQuestions   = array_key_exists('showAllQuestions', $optionRenderForm)
            ? $optionRenderForm['showAllQuestions'] : true;

        //Si le tableau n'est pas vide on le récupère
        if (!is_null($routeRedirection)) {
            $this->routeRedirection = $routeRedirection;
        }

        //Récupération du thème de formulaire
        $this->themeQuestionnaire = $themeQuestionnaire;

        $options = [
            'questionnaire'    => $questionnaire,
            'occurrence'       => $occurrence,
            'user'             => $user,
            'readOnly'         => $readOnly,
            'showAllQuestions' => $showAllQuestions,
            'session'          => 0,
        ];

        return $this->renderForm(
            'nodevo_questionnaire_questionnaire',
            $options,
            'HopitalNumeriqueQuestionnaireBundle:Questionnaire:edit_front.html.twig'
        );
    }

    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param,
     * ajout d'une route de redirection quand tout s'est bien passé.
     *
     * @param HopiUser          $user
     * @param HopiQuestionnaire $questionnaire
     * @param array             $optionRenderForm
     *
     * @return RedirectResponse|Response
     */
    public function editAction(HopiUser $user, HopiQuestionnaire $questionnaire, $optionRenderForm = [])
    {
        $readOnly           = array_key_exists('readOnly', $optionRenderForm) ? $optionRenderForm['readOnly'] : false;
        $routeRedirection   = array_key_exists('routeRedirect', $optionRenderForm) ? $optionRenderForm['routeRedirect']
            : '';
        $themeQuestionnaire = array_key_exists('themeQuestionnaire', $optionRenderForm)
            ? $optionRenderForm['themeQuestionnaire'] : 'default';
        $session            = array_key_exists('session', $optionRenderForm) ? $optionRenderForm['session'] : 0;

        //Si le tableau n'est pas vide on le récupère
        if (!is_null($routeRedirection)) {
            $this->routeRedirection = $routeRedirection;
        }

        //Récupération du thème de formulaire
        $this->themeQuestionnaire = $themeQuestionnaire;

        return $this->renderForm(
            'nodevo_questionnaire_questionnaire',
            [
                'questionnaire' => $questionnaire,
                'user'          => $user,
                'readOnly'      => $readOnly,
                'session'       => $session,
            ],
            'HopitalNumeriqueQuestionnaireBundle:Questionnaire:edit.html.twig'
        );
    }

    /**
     * Export CSV du questionnaire passé en paramètre.
     *
     * @param HopiQuestionnaire $questionnaire Questionnaire à exporter
     *
     * @return Response
     */
    public function exportCSVAction(HopiQuestionnaire $questionnaire)
    {
        //Récupère tout les utilisateurs qui ont répondu à ce questionnaire
        $users = $this->get('hopitalnumerique_user.manager.user')->getUsersByQuestionnaire($questionnaire->getId());

        $results = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->buildForExport(
            $questionnaire->getId(),
            $users
        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $results['colonnes'],
            $results['datas'],
            $questionnaire->getNom() . '-reponses.csv',
            $kernelCharset
        );
    }

    /**
     * Action appelée dans le plugin "Questionnaire" pour tinymce.
     *
     * @return Response
     */
    public function getQuestionnairesAction()
    {
        $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findBy(
            ['lock' => false],
            ['nom' => 'ASC']
        );

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/getQuestionnaires.html.twig', [
            'questionnaires' => $questionnaires,
            'texte' => $this->get('request')->request->get('texte'),
        ]);
    }

    /**
     * Effectue le render des formulaires de Questionnaire.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param array  $options  Tableau d'entité necessaire à l'affichage
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return RedirectResponse|Response
     */
    private function renderForm($formName, $options, $view)
    {
        /** @var User $user */
        $user = $options['user'];
        $readOnly = $options['readOnly'];
        $questionnaire = $options['questionnaire'];
        $occurrence = (array_key_exists('occurrence', $options) ? $options['occurrence'] : null);
        $idSession = $options['session'];

        $label_attr = [
            'idUser' => $user->getId(),
            'idQuestionnaire' => $questionnaire->getId(),
            'occurrence' => $occurrence,
            'routeRedirection' => $this->routeRedirection,
            'readOnly' => $readOnly,
            'idSession' => $idSession,
            'paramId' => $idSession === 0 ? null : $idSession,
        ];


        if (isset($options['showAllQuestions']) && !is_null($options['showAllQuestions'])) {
            $label_attr['showAllQuestions'] = $options['showAllQuestions'];
        }

        //Création du formulaire via le service
        $form = $this->createForm($formName, $questionnaire, [
            'label_attr' => $label_attr,
        ]);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            $routeRedirection = json_decode($form['routeRedirect']->getData(), true);

            //si le formulaire est valide
            if ($form->isValid()) {
                $occurrence = ($form->has('occurrence') ? $form->get('occurrence')->getData() : null);

                //Les champs file uploadés ne sont pas dans params, params ne recupère que les inputs
                $params = $request->get('nodevo_questionnaire_questionnaire');

                //Récupèrations des questions de type files pour le questionnaire courant
                $questionFiles = $this->get('hopitalnumerique_questionnaire.manager.question')->getQuestionsByType(
                    $questionnaire->getId(),
                    'file'
                );

                //Gestion des files uploadés
                $dossierRoot = $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir(
                    $questionnaire->getNomMinifie()
                );

                if (!file_exists($dossierRoot)) {
                    mkdir($dossierRoot);
                }

                $files = [];

                //get All References, and convert to ArrayCollection
                $reponses = new ArrayCollection(
                    $this->get('hopitalnumerique_questionnaire.manager.reponse')
                         ->reponsesByQuestionnaireByUserByFileQuestion(
                             $questionnaire->getId(),
                             $user->getId(),
                             $occurrence
                         )
                );

                //Parcourt les questions de champ file
                foreach ($questionFiles as $key => $questionFile) {
                    $fileName = 'undefined';
                    //Récupère la réponse de la question courante
                    $criteria = Criteria::create()->where(Criteria::expr()->eq('question', $questionFile));
                    //Récupération d'un tableau comportant une seule réponse
                    $tempReponse = $reponses->matching($criteria);

                    // -v-v-v- GME 26/02/2014 : Traitement brouillon, le array_shift ou reset ne fonctionne pas -v-v-v-
                    $test = [];
                    foreach ($tempReponse as $temp) {
                        $test[] = $temp;
                        break;
                    }
                    $reponse = !empty($test) ? $test[0] : null;
                    // -^-^-^- Traitement brouillon, le array_shift ou reset ne fonctionne pas -^-^-^-


                    //Si il n'y a pas de réponses pour cette question pour cet utilisateur
                    if (is_null($reponse)) {
                        $reponse = new Reponse();
                        $reponse->setUser($user);
                        $reponse->setQuestion($questionFile);
                        $reponse->setOccurrence($occurrence);
                    }

                    //Format du champ file
                    $champFile = $questionFile->getTypeQuestion()->getLibelle()
                                 . '_'
                                 . $questionFile->getId()
                                 . '_'
                                 . $questionFile->getAlias()
                    ;

                    /** @var UploadedFile $file */
                    $file = $form[$champFile]->getData();

                    if (!is_null($file)) {
                        if ($file->getSize() > 5000000) {
                            $this->addFlash('danger', 'Les fichiers uploadés ne peuvent pas dépasser 5Mo.');

                            return $this->redirect($request->headers->get('referer'));
                        }

                        $fileName = $questionnaire->getId()
                                    . '_'
                                    . $questionFile->getId()
                                    . '_'
                                    . $user->getId()
                                    . '_'
                                    . date_timestamp_get(new \DateTime())
                                    . '.'
                                    . $file->guessExtension()
                        ;
                    }

                    if (true === $form[$champFile.'-remove']->getData()) {
                        unset($params[$form[$champFile.'-remove']->getName()]);
                        $this->get('hopitalnumerique_questionnaire.manager.reponse')->removeResponseFile(
                            $questionnaire->getNomMinifie(),
                            $reponse->getReponse()
                        );

                        $reponse->setReponse(null);
                    }


                    if (!is_null($file)) {
                        $files[$questionFile->getAlias()] = [
                            'nom'     => $fileName,
                            'file'    => $file,
                            'reponse' => $reponse,
                        ];

                        //MAJ/ajout du nouveau path
                        $reponse->setReponse(
                            $fileName
                        );
                    }
                }

                $reponses = [];

                //Parcourt les fichiers uploadés
                foreach ($files as $file) {
                    //Si le JS est désactivé, il se peut qu'il n'y ait pas de fichier uploadé
                    if (is_null($file['file'])) {
                        break;
                    }

                    $file['file']->move($dossierRoot, $file['nom']);

                    $reponses[] = $file['reponse'];
                }

                //Mise à jour / créations des réponses correspondantent aux fichiers
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save($reponses);

                //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
                $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser(
                    $questionnaire->getId(),
                    $user->getId(),
                    true,
                    $occurrence
                );

                $new = true;

                //Gestion des réponses
                foreach ($params as $key => $param) {
                    //Récupération de l'id de la question, la clé est sous la forme : "type_id_alias"
                    $arrayParamKey = explode('_', $key);

                    //Le tableau de arrayParamKey : 0 => type du champ - 1 => Id de la question - 2+=> alias du champ
                    $typeParam = isset($arrayParamKey) && array_key_exists(0, $arrayParamKey) ? $arrayParamKey[0] : '';
                    $idQuestion = isset($arrayParamKey) && array_key_exists(1, $arrayParamKey) ? $arrayParamKey[1] : 0;

                    // Si l'id de la question n'a pas été récupéré alors on ne sauvegarde pas la question
                    // (exemple avec le cas particulier du token du formulaire)
                    if (0 === $idQuestion || '' === $idQuestion || '_token' === $key) {
                        continue;
                    }

                    //récupération de la réponse courante
                    $reponse = array_key_exists($idQuestion, $reponses) ? $reponses[$idQuestion] : null;

                    //Mode ajout
                    if (is_null($reponse)) {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion(
                            $this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(
                                ['id' => $idQuestion]
                            )
                        );
                        $reponse->setOccurrence($occurrence);
                    }
                    //Mode ajout + édition : set la nouvelle réponse
                    $reponse->setReponse($param);

                    if ('entity' === $typeParam || 'entityradio' === $typeParam) {
                        $reponse->setReference(
                            $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => $param])
                        );
                    } elseif ('etablissement' === $typeParam) {
                        $reponse->setEtablissement(
                            $this->get('hopitalnumerique_etablissement.manager.etablissement')->findOneBy(
                                ['id' => $param]
                            )
                        );
                    } elseif ('etablissementmultiple' === $typeParam) {
                        if (is_null($reponse->getEtablissementMulitple())) {
                            $reponse->setEtablissementMulitple([]);
                            foreach ($param as $value) {
                                $reponse->addEtablissementMulitple(
                                    $this->get('hopitalnumerique_etablissement.manager.etablissement')->findOneBy(
                                        ['id' => $value]
                                    )
                                );
                            }
                        }
                        $reponse->setReponse('');
                    } elseif ('entitymultiple' === $typeParam || 'entitycheckbox' === $typeParam) {
                        if (is_null($reponse->getReferenceMulitple())) {
                            $reponse->setReferenceMulitple([]);
                            foreach ($param as $value) {
                                $reponse->addReferenceMulitple(
                                    $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(
                                        ['id' => $value]
                                    )
                                );
                            }
                        }
                        $reponse->setReponse('');
                    }

                    if ('module-evaluation' === $questionnaire->getNomMinifie()) {
                        $idSession = $form['idSession']->getData();

                        if (!is_null($idSession) && 0 !== $idSession) {
                            $reponse->setParamId($idSession);
                        }
                    }

                    //Test ajout ou edition
                    $new = is_null($reponse->getId());

                    //Mise à jour de la réponse dans le tableau des réponses
                    $reponses[$idQuestion] = $reponse;
                }

                if ('module-evaluation' === $questionnaire->getNomMinifie()) {
                    $idSession = $form['idSession']->getData();

                    //Dans le cas où on est dans le formulaire de session
                    $session = ($idSession !== 0) ? $this->get('hopitalnumerique_module.manager.session')->findOneBy(
                        ['id' => $idSession]
                    ) : null;

                    if (!is_null($session)) {
                        //Modifications de l'inscription: modification du statut "etatEvaluer"
                        $inscription = $this->get('hopitalnumerique_module.manager.inscription')->findOneBy(
                            ['user' => $user, 'session' => $session]
                        );
                        $inscription->setEtatEvaluation(
                            $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 29])
                        );

                        // Vérification de l'ensemble des inscriptions de la session :
                        // si toutes les inscriptions sont évaluée alors la session est archiver
                        $sessionAArchiver = false;
                        if ($session->getDateSession() < new \DateTime()) {
                            $sessionAArchiver = true;
                            /** @var Inscription $inscription */
                            foreach ($session->getInscriptions() as $inscription) {
                                if (407 === $inscription->getEtatInscription()->getId()
                                    && 411 === $inscription->getEtatParticipation()->getId()
                                ) {
                                    if (29 !== $inscription->getEtatEvaluation()->getId()) {
                                        $sessionAArchiver = false;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($sessionAArchiver) {
                            $session->setArchiver(true);
                            $this->get('hopitalnumerique_module.manager.session')->save($session);
                        }

                        $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

                        $roleUser = $this->get('nodevo_role.manager.role')->getUserRole($user);

                        // Mise à jour de la production du module dans la liste des productions maitrisées :
                        // uniquement pour les ambassadeurs
                        if ('ROLE_AMBASSADEUR_7' === $roleUser) {
                            //Récupération des formations
                            $formations = $session->getModule()->getProductions();

                            //Pour chaque production on ajout l'utilisateur à la liste des ambassadeurs qui la maitrise
                            /** @var Objet $formation */
                            foreach ($formations as $formation) {
                                // Récupération des ambassadeurs pour vérifier si
                                // l'utilisateur actuel ne maitrise pas déjà cette formation
                                $ambassadeursFormation = $formation->getAmbassadeurs();
                                $ambassadeurIds = [];

                                foreach ($ambassadeursFormation as $ambassadeur) {
                                    $ambassadeurIds[] = $ambassadeur->getId();
                                }

                                if (!in_array($user->getId(), $ambassadeurIds)) {
                                    $formation->addAmbassadeur($user);
                                    $this->get('hopitalnumerique_objet.manager.objet')->save($formation);
                                }
                            }

                            // Pour chaque connaissance associé à la session,
                            // on les associe à l'ambassadeur qui vient de remplir le formulaire d'évaluation
                            $connaissances = $session->getConnaissances();

                            foreach ($connaissances as $connaissanceSession) {
                                $connaissance = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur_si')
                                                     ->findOneBy(['user' => $user, 'domaine' => $connaissanceSession])
                                ;

                                //Si il a deja cette connaissance, on ne le rajoute pas
                                if (!is_null($connaissance)) {
                                    continue;
                                }

                                $connaissance = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur_si')
                                                     ->createEmpty()
                                ;

                                $connaissance->setUser($user);
                                $connaissance->setConnaissance(
                                    $this->get('hopitalnumerique_reference.manager.reference')->findBy(
                                        ['code' => 'CONNAISSANCES_AMBASSADEUR'],
                                        ['order' => 'ASC']
                                    )[1]
                                );

                                $connaissance->setDomaine($connaissanceSession);
                            }
                        }
                    }
                }
                //Envoie du mail à l'utilisateur pour l'alerter de la validation de sa candidature
                if ($this->envoieDeMail) {
                    switch ($questionnaire->getNomMinifie()) {
                        case 'expert':
                            //Expert
                            $mailExpert = $this->get('nodevo_mail.manager.mail')->sendCandidatureExpertMail($user);
                            $this->get('mailer')->send($mailExpert);

                            //send Mail to all admins
                            $candidature = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')
                                                ->getQuestionnaireFormateMail($reponses)
                            ;

                            //Récupération de l'adresse mail en parameter.yml
                            $adressesMails = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')
                                                  ->getMailExpertReponses()
                            ;

                            if (!is_null($adressesMails)) {
                                $variablesTemplate = [
                                    'candidat' => $user->getFirstname() . ' ' . $user->getLastname(),
                                    'questionnaire' => $candidature,
                                ];

                                $mailsExperts = $this->get('nodevo_mail.manager.mail')->sendCandidatureExpertAdminMail(
                                    $adressesMails,
                                    $variablesTemplate
                                );

                                foreach ($mailsExperts as $mailExperts) {
                                    $this->get('mailer')->send($mailExperts);
                                }
                            }

                            break;
                        case 'ambassadeur':
                            //Ambassadeur
                            $mailAmbassadeur = $this->get('nodevo_mail.manager.mail')->sendCandidatureAmbassadeurMail(
                                $user
                            );
                            $this->get('mailer')->send($mailAmbassadeur);

                            //CMSI
                            $candidature = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')
                                                ->getQuestionnaireFormateMail($reponses)
                            ;

                            $etablissement = is_null($user->getOrganization())
                                ? $user->getOrganizationLabel()
                                : $user->getOrganization()->getNom()
                            ;

                            $candidat = '<ul>';
                            $candidat .= '<li><strong>Prénom</strong> : ' . (trim($user->getFirstname()) === '' ? '-' : $user->getFirstname()) . '</li>';
                            $candidat .= '<li><strong>Nom</strong> : ' . (trim($user->getLastname()) == '' ? '-' : $user->getLastname()) . '</li>';
                            $candidat .= '<li><strong>Adresse e-mail</strong> : ' . (trim($user->getEmail()) === '' ? '-' : $user->getEmail()) . '</li>';
                            $candidat .= '<li><strong>Téléphone direct</strong> : ' . (trim($user->getPhoneNumber()) === '' ? '-' : $user->getPhoneNumber()) . '</li>';
                            $candidat .= '<li><strong>Téléphone portable</strong> : ' . (trim($user->getCellPhoneNumber()) === '' ? '-' : $user->getCellPhoneNumber()) . '</li>';
                            $candidat .= '<li><strong>Profil</strong> : ' . (trim($user->getProfileType()->getLibelle()) === '' ? '-' : $user->getProfileType()->getLibelle()) . '</li>';
                            $candidat .= '<li><strong>Structure de rattrachement</strong> : ' . (trim($etablissement) === '' ? '-' : $etablissement) . '</li>';
                            $candidat .= '<li><strong>Nom de votre structure si non disponible dans la liste précédente</strong> : ' . (trim($user->getOrganizationLabel()) === '' ? '-' : $user->getOrganizationLabel()) . '</li>';
                            $candidat .= '<li><strong>Fonction dans l\'établissement</strong> : ' . (trim($user->getJobLabel()) === '' ? '-' : $user->getJobLabel()) . '</li>';
                            $candidat .= '</ul>';

                            $CMSI = $this->get('hopitalnumerique_user.manager.user')->findUsersByRoleAndRegion(
                                $user->getRegion(),
                                'ROLE_ARS_CMSI_4'
                            );

                            if (!is_null($CMSI)) {
                                $variablesTemplate = [
                                    'candidat' => $candidat,
                                    'questionnaire' => $candidature,
                                ];
                                $mailCMSI = $this->get('nodevo_mail.manager.mail')->sendCandidatureAmbassadeurCMSIMail(
                                    $CMSI,
                                    $variablesTemplate
                                );

                                $this->get('mailer')->send($mailCMSI);
                            }
                            break;
                        default:
                            // Récupère les questions / réponses formatées correctement
                            // pour l'affichage dans les mails génériques
                            $candidature = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')
                                                ->getQuestionnaireFormateMail($reponses)
                            ;

                            //Formate les données de l'utilisateur qui a répondu au questionnaire
                            $etablissement = is_null($user->getOrganization())
                                ? $user->getOrganizationLabel()
                                : $user->getOrganization()->getNom()
                            ;

                            $candidat = '<ul>';
                            $candidat .= '<li><strong>Prénom</strong> : ' . (trim($user->getFirstname()) === '' ? '-' : $user->getFirstname()) . '</li>';
                            $candidat .= '<li><strong>Nom</strong> : ' . (trim($user->getLastname()) == '' ? '-' : $user->getLastname()) . '</li>';
                            $candidat .= '<li><strong>Adresse e-mail</strong> : ' . (trim($user->getEmail()) === '' ? '-' : $user->getEmail()) . '</li>';
                            $candidat .= '<li><strong>Téléphone direct</strong> : ' . (trim($user->getPhoneNumber()) === '' ? '-' : $user->getPhoneNumber()) . '</li>';
                            $candidat .= '<li><strong>Téléphone portable</strong> : ' . (trim($user->getCellPhoneNumber()) === '' ? '-' : $user->getCellPhoneNumber()) . '</li>';
                            $candidat .= '<li><strong>Profil</strong> : ' . (null === $user->getProfileType() || trim($user->getProfileType()->getLibelle()) === '' ? '-' : $user->getProfileType()->getLibelle()) . '</li>';
                            $candidat .= '<li><strong>Structure de rattrachement</strong> : ' . (trim($etablissement) === '' ? '-' : $etablissement) . '</li>';
                            $candidat .= '<li><strong>Nom de votre structure si non disponible dans la liste précédente</strong> : ' . (trim($user->getOrganizationLabel()) === '' ? '-' : $user->getOrganizationLabel()) . '</li>';
                            $candidat .= '<li><strong>Fonction dans l\'établissement</strong> : ' . (trim($user->getJobLabel()) === '' ? '-' : $user->getJobLabel()) . '</li>';
                            $candidat .= '</ul>';

                            //Récupération de l'adresse ml du domaine
                            $domain = $this
                                ->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
                            ;

                            $adressesMails[$domain->getAdresseMailContact()] = $domain->getNom();

                            //Set des variables du gabarit du mail
                            $variablesTemplate = [
                                'nomQuestionnaire' => $questionnaire->getNom(),
                                'candidat' => $candidat,
                                'questionnaire' => $candidature,
                            ];

                            $mailsAEnvoyer = $this->get('nodevo_mail.manager.mail')->sendReponsesQuestionnairesMail(
                                $adressesMails,
                                $variablesTemplate
                            );


                            /** @var \Swift_Message $mailAEnvoyer */
                            foreach ($mailsAEnvoyer as $mailAEnvoyer) {
                                foreach ($files as $file) {
                                    $filePath = $this->get('hopitalnumerique_questionnaire.manager.reponse')
                                                     ->getUploadRootDir(
                                                         $questionnaire->getNomMinifie()
                                                     ) . '/' . $file['nom']
                                    ;

                                    $mailAEnvoyer->attach(\Swift_Attachment::fromPath($filePath));
                                }

                                $this->get('mailer')->send($mailAEnvoyer);
                            }
                            break;
                    }
                }
                //Mise à jour/création des réponses
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save($reponses);

                if ($this->get('session')->has(self::REDIRECT_REFERER_SESSION_KEY)) {
                    $redirect = $this->get('session')->get(self::REDIRECT_REFERER_SESSION_KEY);
                    $this->get('session')->remove(self::REDIRECT_REFERER_SESSION_KEY);
                    $this->addFlash(($new ? 'success' : 'info'), 'Formulaire enregistré.');

                    return $this->redirect($redirect);
                }

                if (!is_null($questionnaire->getLien()) && trim($questionnaire->getLien() !== '')) {
                    return $this->redirect($questionnaire->getLien());
                }

                $this->addFlash(($new ? 'success' : 'info'), 'Formulaire enregistré.');

                $action = 'validate';
                $class = 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire';

                $this->container->get('hopitalnumerique_core.log')->Logger(
                    $action,
                    $questionnaire,
                    $questionnaire->getNom(),
                    $class,
                    $this->getUser()
                );

                //Sauvegarde / Sauvegarde + quitte
                $do = $request->request->get('do');

                return $this->redirect(
                    $do == 'save-close'
                        ? $this->generateUrl($routeRedirection['quit']['route'], $routeRedirection['quit']['arguments'])
                        : ('save-add' == $do
                        ? $this->generateUrl(
                            'hopitalnumerique_questionnaire_occurrence_add',
                            ['questionnaire' => $questionnaire->getId()]
                        )
                        : $this->generateUrl(
                            $routeRedirection['sauvegarde']['route'],
                            $routeRedirection['sauvegarde']['arguments']
                        ))
                );
            }

            $erreur = '';
            foreach ($form->getErrors(true) as $error) {
                $erreur = $error->getOrigin()->getConfig()->getOptions()['label'] . ' ' . $error->getMessage();
            }

            $this->addFlash(('danger'), $erreur);

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'questionnaire' => $questionnaire,
            'user' => $user,
            'theme' => $this->themeQuestionnaire,
        ]);
    }

    /**
     * Effectue le render du formulaire Module.
     *
     * @param                   $formName
     * @param HopiQuestionnaire $questionnaire
     *
     * @return RedirectResponse|Response
     */
    private function renderGestionForm($formName, HopiQuestionnaire $questionnaire)
    {
        $questionnaireEstOccurrenceMultiple = $questionnaire->isOccurrenceMultiple();

        //Création du formulaire via le service
        $form = $this->createForm($formName, $questionnaire);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($questionnaire->getId());

                $this->processFormOccurrenceMultipleChange($questionnaire, $questionnaireEstOccurrenceMultiple);

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->save($questionnaire);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->addFlash(
                    ($new ? 'success' : 'info'),
                    'Questionnaire ' . ($new ? 'ajouté.' : 'mis à jour.')
                );

                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');

                return $this->redirect(
                    ($do == 'save-close'
                        ? $this->generateUrl('hopitalnumerique_questionnaire_index')
                        : $this->generateUrl(
                            'hopitalnumerique_questionnaire_edit_questionnaire',
                            ['id' => $questionnaire->getId()]
                        ))
                );
            }
        }

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Questionnaire:Gestion/edit.html.twig', [
            'form' => $form->createView(),
            'questionnaire' => $questionnaire,
            'isOccurrenceMultiple' => $questionnaireEstOccurrenceMultiple,
            'theme' => 'vertical',
        ]);
    }

    /**
     * Effectue les traitements nécessaires selon la modification de l'occurrence multiple lors de la sauvegarde.
     *
     * @param Questionnaire $questionnaire
     * @param bool $questionnaireEstOccurrenceMultipleOriginal Valeur de l'occurrence multiple avant soumission du form
     */
    private function processFormOccurrenceMultipleChange(
        HopiQuestionnaire $questionnaire,
        $questionnaireEstOccurrenceMultipleOriginal
    ) {
        // On a décoché l'occurrence multiple
        if (!$questionnaireEstOccurrenceMultipleOriginal && $questionnaire->isOccurrenceMultiple()) {
            $this->container->get('hopitalnumerique_questionnaire.manager.questionnaire')->forceOccurrenceMultiple(
                $questionnaire
            );
        } elseif ($questionnaireEstOccurrenceMultipleOriginal
                  && !$questionnaire->isOccurrenceMultiple()
        ) { // On a coché l'occurrence multiple
            $this->container->get('hopitalnumerique_questionnaire.manager.questionnaire')->deleteOccurrencesMultiples(
                $questionnaire
            );
        }
    }
}
