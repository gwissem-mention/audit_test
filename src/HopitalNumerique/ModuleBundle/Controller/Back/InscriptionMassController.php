<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Inscription des actions de mass controller.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionMassController extends Controller
{
    /**
     * Action de masse sur l'état d'inscription.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function accepterInscriptionMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 407, 'inscription');
    }

    /**
     * Action de masse sur l'état d'inscription.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function refuserInscriptionMassAction(Request $request, $primaryKeys, $allPrimaryKeys)
    {
        //Récupération des cookies
        $cookies = $request->cookies;
        $textMail = $cookies->has('textMailInscription') ? $cookies->get('textMailInscription') : 'pasDeCookie';

        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 408, 'inscription', ['textMailInscription' => $textMail]);
    }

    /**
     * Action de masse sur l'état d'inscription.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function annulerInscriptionMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 409, 'inscription');
    }

    /**
     * Action de masse sur l'état de participation.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function aParticiperParticipationMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 411, 'participation');
    }

    /**
     * Action de masse sur l'état de participation.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function aPasParticiperParticipationMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 412, 'participation');
    }

    /**
     * Action de masse sur l'état de l'évaluation.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function aEvaluaerEvaluationMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 28, 'evaluation');
    }

    /**
     * Action de masse sur l'état de l'évaluation.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function evalueeEvaluationMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 29, 'evaluation');
    }

    /**
     * Action de masse sur l'état de l'évaluation.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function naEvaluationMassAction($primaryKeys, $allPrimaryKeys)
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 430, 'evaluation');
    }

    /**
     * Envoyer un mail aux utilisateurs.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function envoyerMailMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected inscription
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_module.grid.inscription')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy(['id' => $primaryKeys]);

        //get emails
        $list = [];
        foreach ($inscriptions as $inscription) {
            if ($inscription->getUser()->getEmail() != '') {
                $list[] = $inscription->getUser()->getEmail();
            }
        }

        //to
        $to = $this->get('security.context')->getToken()->getUser()->getEmail();

        //bcc list
        $bcc = join(',', $list);

        return $this->render('HopitalNumeriqueModuleBundle:Back/Inscription:mailto.html.twig', [
            'mailto' => 'mailto:' . $to . '?bcc=' . $bcc,
            'list' => $list,
        ]);
    }

    /**
     * Envoyer un mail de relance d'évaluation aux utilisateurs.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function sendMailEvaluationMassAction($primaryKeys, $allPrimaryKeys)
    {
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_module.grid.inscription')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy(['id' => $primaryKeys]);

        if (count($inscriptions) > 0) {
            $mails = $this->get('nodevo_mail.manager.mail')->sendFormulaireEvaluationsMassMail($inscriptions, []);
            foreach ($mails as $mail) {
                $this->get('mailer')->send($mail);
            }

            $this->get('session')->getFlashBag()->add('success', 'Mail(s) envoyé(s) avec succès.');
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    /**
     * Suppression de masse des inscriptions.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_module.manager.inscription')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy(['id' => $primaryKeys]);

        $this->get('hopitalnumerique_module.manager.inscription')->delete($inscriptions);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    /**
     * Action de masse sur les états.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     * @param int   $idReference    Id de la référence à mettre à la place
     *
     * @return Redirect
     */
    private function toggleEtat($primaryKeys, $allPrimaryKeys, $idReference, $etat, $options = [])
    {
        //check connected user ACL
        $user = $this->get('security.context')->getToken()->getUser();

        if ($this->get('nodevo_acl.manager.acl')->checkAuthorization($this->generateUrl('hopital_numerique_user_delete', ['id' => 1]), $user) == -1) {
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas les droits suffisants pour activer des utilisateurs.');

            return $this->redirect($this->generateUrl('hopitalnumerique_module_module'));
        }

        //get all selected inscription
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_module.grid.inscription')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy(['id' => $primaryKeys]);

        //récupère une inscription pour récuperer les module / session (php < php5.4)
        $tempInscription = $inscriptions[0];

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => $idReference]);
        switch ($etat) {
            case 'inscription':
                $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatInscription($inscriptions, $ref);
                if (407 === $ref->getId()) {
                    //Envoyer mail d'acceptation de l'inscription
                    $mails = $this->get('nodevo_mail.manager.mail')->sendAcceptationInscriptionMassMail($inscriptions, []);
                    foreach ($mails as $mail) {
                        $this->get('mailer')->send($mail);
                    }
                } elseif (408 === $ref->getId()) {
                    $textRefus = $options['textMailInscription'];
                    //Envoyer mail de refus de l'inscription
                    $mails = $this->get('nodevo_mail.manager.mail')->sendRefusInscriptionMassMail($inscriptions, ['textRefus' => $textRefus]);
                    foreach ($mails as $mail) {
                        $this->get('mailer')->send($mail);
                    }
                }
                if (408 === $ref->getId() || 409 === $ref->getId()) {
                    $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatParticipation($inscriptions, $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 412]));
                    $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation($inscriptions, $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 430]));
                }
                //inform user connected
                $this->get('session')->getFlashBag()->add('info', 'Inscription(s) modifiée(s) avec succès.');
                break;
            case 'participation':
                $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatParticipation($inscriptions, $ref);
                //A participé
                if (411 === $ref->getId()) {
                    //Envoyer mail du formulaire d'évluation de la session
                    $mails = $this->get('nodevo_mail.manager.mail')->sendFormulaireEvaluationsMassMail($inscriptions, []);
                    foreach ($mails as $mail) {
                        $this->get('mailer')->send($mail);
                    }
                    $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation($inscriptions, $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 28]));
                }
                //N'a pas participé
                elseif (412 === $ref->getId()) {
                    $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation($inscriptions, $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 27]));
                }
                //inform user connected
                $this->get('session')->getFlashBag()->add('info', 'Participation(s) modifiée(s) avec succès.');
                break;
            case 'evaluation':
                $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation($inscriptions, $ref);
                if ($ref->getId() == '29') {
                    foreach ($inscriptions as $inscription) {
                        $roleUser = $this->get('nodevo_role.manager.role')->getUserRole($inscription->getUser());
                        //Mise à jour de la production du module dans la liste des productions maitrisées : uniquement pour les ambassadeurs
                        if ('ROLE_AMBASSADEUR_7' === $roleUser) {
                            //Récupération des formations
                            $formations = $inscription->getSession()->getModule()->getProductions();

                            //Pour chaque production on ajout l'utilisateur à la liste des ambassadeurs qui la maitrise
                            foreach ($formations as $formation) {
                                //Récupération des ambassadeurs pour vérifier si l'utilisateur actuel ne maitrise pas déjà cette formation
                                $ambassadeursFormation = $formation->getAmbassadeurs();
                                $ambassadeurIds = [];

                                foreach ($ambassadeursFormation as $ambassadeur) {
                                    $ambassadeurIds[] = $ambassadeur->getId();
                                }

                                if (!in_array($inscription->getUser()->getId(), $ambassadeurIds)) {
                                    $formation->addAmbassadeur($inscription->getUser());
                                    $this->get('hopitalnumerique_objet.manager.objet')->save($formation);
                                }
                            }
                        }
                    }
                }
                //inform user connected
                $this->get('session')->getFlashBag()->add('info', 'Evaluation(s) modifiée(s) avec succès.');
                break;
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_module_module_session_inscription', ['id' => $tempInscription->getSession()->getId()]));
    }
}
