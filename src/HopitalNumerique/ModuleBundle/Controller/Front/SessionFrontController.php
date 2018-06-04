<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SessionFrontController extends Controller
{
    /**
     * Affiche la description d'une session dans une popin.
     *
     * @param Session $session Session à afficher
     *
     * @return Response
     */
    public function descriptionAction(Session $session)
    {
        $connaissances = $session->getConnaissances();
        $connaissancesOrderedByParent = [];

        foreach ($connaissances as $connaissance) {
            if (!array_key_exists($connaissance->getFirstParent()->getId(), $connaissancesOrderedByParent)) {
                $connaissancesOrderedByParent[$connaissance->getFirstParent()->getId()] = [];
            }

            $connaissancesOrderedByParent[$connaissance->getFirstParent()->getId()][] = $connaissance;
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:description.html.twig', [
                'session' => $session,
                'connaissances' => $connaissancesOrderedByParent,
        ]);
    }

    /**
     * Liste toutes les informations de la session.
     *
     * @param Session $session
     *
     * @return Response
     */
    public function informationAction(Session $session)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        if (($session->getNombrePlaceDisponible() - count($session->getInscriptions())) == 0
            && !$session->userIsInscrit($user)
        ) {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas :
            // suppression manuelle sur le serveur

            $this->addFlash('danger', 'Cette session est complète, vous ne pouvez pas vous inscrire. Veuillez-choisir une autre session de ce module thèmatique.');
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:index.html.twig', [
                'session' => $session,
                'moduleSelectionne' => $session->getModule(),
        ]);
    }

    /**
     * Envoie un mail de rappel à tout les utilisateurs inscrits et acceptés de la session.
     *
     * @param Session $session
     *
     * @return Response
     */
    public function mailRappelAction(Session $session)
    {
        //récupérations des inscriptions acceptées
        $inscriptions = $session->getInscriptionsAccepte();

        //Envoyer mail de refus de l'inscription
        $mails = $this->get('nodevo_mail.manager.mail')->sendRappelInscriptionMail($inscriptions, []);
        $i = 0;
        foreach ($mails as $mail) {
            $mail_send = $this->get('mailer')->send($mail);
            if ($mail_send[0] == null) {
                ++$i;
            }
        }

        // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas :
        // suppression manuelle sur le serveur
        $this->addFlash('success', 'Mails de rappel envoyé aux utilisateurs acceptés à cette session.');

        if ($i != 0) {
            $this->addFlash('danger', "Le mail n'a pas été envoyé à " . $i . ' utilisateur(s) inactif(s).');
        }

        return new Response('Mails de rappel envoyés.');
    }

    /**
     * Compte HN : Génère le fichier CSV des formulaires d'évaluation.
     *
     * @param Session $session
     *
     * @return Response
     */
    public function evaluationAction(Session $session)
    {
        $colonnes = [];
        $datas = [];

        $inscriptions = $session->getInscriptionsAccepte();

        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $hasReponses = false;
            $user = $inscription->getUser();
            $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser(
                4,
                $user->getId(),
                true,
                null,
                $session->getId()
            );
            $row = [];

            /** @var Reponse $reponse */
            foreach ($reponses as $reponse) {
                $question = $reponse->getQuestion();
                $idQuestion = $question->getId();

                //ajoute la question si non présente dans les colonnes
                if (!isset($colonnes[$idQuestion])) {
                    $colonnes[$idQuestion] = $question->getLibelle();
                }

                //handle la réponse
                switch ($question->getTypeQuestion()->getLibelle()) {
                    case 'checkbox':
                        $row[$idQuestion] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non');
                        break;
                    case 'entityradio':
                        $referenceReponse = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(
                            ['id' => $reponse->getReponse()]
                        );

                        if (!is_null($referenceReponse)) {
                            $row[$idQuestion] = $referenceReponse->getLibelle();
                        } else {
                            $row[$idQuestion] = 'Non renseigné';
                        }
                        break;
                    default:
                        $row[$idQuestion] = $reponse->getReponse();
                        break;
                }

                $hasReponses = true;
            }

            if (!$hasReponses) {
                continue;
            }

            ksort($row);

            $datas[] = $row;
        }

        if (empty($datas)) {
            $colonnes = [0 => 'Aucune donnée'];
            $datas[] = [0 => ''];
        }

        //reorder colonnes
        ksort($colonnes);

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv(
            $colonnes,
            $datas,
            'export-evaluations.csv',
            $kernelCharset
        );
    }

    /**
     * POPIN : Partage de resultat.
     *
     * @param Session $session
     *
     * @return Response
     */
    public function parametrageAction(Session $session)
    {
        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:fancy.html.twig', [
            'session' => $session,
        ]);
    }

    /**
     * POPIN : gestion de la présence des experts.
     *
     * @param Session $session
     *
     * @return Response
     */
    public function parametrageSaveAction(Session $session)
    {
        //Mise à jour de la présence des experts
        $inscriptionsId = json_decode($this->get('request')->request->get('inscriptions'));

        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy(['session' => $session]);
        $refParticipation = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(SessionStatus::STATUT_PARTICIPATION_OK_ID);
        $refPasParticipation = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(SessionStatus::STATUT_PARTICIPATION_KO_ID);
        $refEval = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(28);
        $refEvalCanceled = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(430);

        $mails = [];
        /** @var Inscription $inscription */
        foreach ($inscriptions as &$inscription) {
            if (in_array($inscription->getId(), $inscriptionsId)) {
                $etatparticip = $inscription->getEtatParticipation()->getId();
                $inscription->setEtatParticipation($refParticipation);
                $inscription->setEtatEvaluation($refEval);
                //Envoyer mail du formulaire d'évluation de la session
                if (SessionStatus::STATUT_PARTICIPATION_OK_ID != $etatparticip) {
                    $mails = array_merge(
                        $mails,
                        $this->get('nodevo_mail.manager.mail')->sendFormulaireEvaluationsMassMail([$inscription], [])
                    );
                }
            } else {
                $inscription->setEtatParticipation($refPasParticipation);
                $inscription->setEtatEvaluation($refEvalCanceled);
            }
        }

        foreach ($mails as $mail) {
            $this->get('mailer')->send($mail);
        }

        $this->get('hopitalnumerique_module.manager.inscription')->save($inscriptions);

        return new Response('{"success":true}', 200);
    }

    /**
     * Compte HN : Génère le fichier CSV des formulaires d'évaluation.
     *
     * @param User $user
     *
     * @return Response
     */
    public function exportCommentaireCSVAction(User $user)
    {
        $datas = [];

        //get sessions terminées where user connected == formateur
        $currentDomain = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsForFormateur($user, $currentDomain);

        $colonnes = [
            'Module',
            'Date de la session',
            'Utilisateur',
            'Date de l\'inscription',
            'Statut inscription',
            'Commentaire',
        ];

        //Pour chaque session, on parcourt les inscriptions pour les lister
        /** @var Session $session */
        foreach ($sessions as $session) {
            /** @var Inscription $inscription */
            foreach ($session->getInscriptions() as $inscription) {
                $row = [];

                $row[0] = $session->getModule()->getTitre();
                $row[1] = $session->getDateSession()->format('d/m/Y');
                $row[2] = $inscription->getUser()->getAppellation();
                $row[3] = $inscription->getDateInscription()->format('d/m/Y');
                $row[4] = $inscription->getEtatInscription()->getLibelle();
                $row[5] = $inscription->getCommentaire();

                $datas[] = $row;
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv(
            $colonnes,
            $datas,
            'export-commentaire-formateur.csv',
            $kernelCharset
        );
    }

    /**
     * Compte HN : Génère le fichier CSV des formulaires d'évaluation par session.
     *
     * @param Session $session
     *
     * @return Response
     */
    public function exportCommentaireCSVBySessionAction(Session $session)
    {
        $datas = [];

        $colonnes = [
            'Module',
            'Date de la session',
            'Utilisateur',
            'Etablissement',
            'Région',
            'Adresse mail',
            'Fonction',
            'Libellé de la fonction',
            'Date de l\'inscription',
            'Statut inscription',
            'Commentaire',
        ];

        /** @var Inscription $inscription */
        foreach ($session->getInscriptions() as $inscription) {
            $row = [];

            $row[0] = $session->getModule()->getTitre();
            $row[1] = $session->getDateSession()->format('d/m/Y');
            $row[2] = $inscription->getUser()->getAppellation();
            $row[3] = !is_null($inscription->getUser()->getOrganization())
                ? $inscription->getUser()->getOrganization()->getNom()
                : ($inscription->getUser()->getOrganizationLabel())
            ;
            $row[4] = !is_null($inscription->getUser()->getRegion())
                ? $inscription->getUser()->getRegion()->getLibelle()
                : '-'
            ;
            $row[5] = $inscription->getUser()->getEmail();
            $row[6] = !is_null($inscription->getUser()->getJobType())
                ? $inscription->getUser()->getJobType()->getLibelle()
                : '-'
            ;
            $row[7] = $inscription->getUser()->getJobLabel();
            $row[8] = $inscription->getDateInscription()->format('d/m/Y');
            $row[9] = $inscription->getEtatInscription()->getLibelle();
            $row[10] = $inscription->getCommentaire();

            $datas[] = $row;
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv(
            $colonnes,
            $datas,
            'export-commentaire-formateur-session.csv',
            $kernelCharset
        );
    }
}
