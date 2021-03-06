<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

use HopitalNumerique\ModuleBundle\Entity\Module;
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Inscription controller.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionController extends Controller
{
    /**
     * Affiche la liste des Inscription.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAction(\HopitalNumerique\ModuleBundle\Entity\Session $session)
    {
        $grid = $this->get('hopitalnumerique_module.grid.inscription');
        $grid->setSourceCondition('session', $session->getId());

        return $grid->render('HopitalNumeriqueModuleBundle:Back/Inscription:index.html.twig', ['session' => $session]);
    }

    /**
     * Affiche la liste des Inscriptions.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAllInscriptionsAction()
    {
        $grid = $this->get('hopitalnumerique_module.grid.allinscription');

        return $grid->render('HopitalNumeriqueModuleBundle:Back/Inscription:indexAllInscriptions.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Inscriptions.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function addAction(\HopitalNumerique\ModuleBundle\Entity\Session $session)
    {
        $inscription = $this->get('hopitalnumerique_module.manager.inscription')->createEmpty();
        //Valeurs par défaut lors de la création
        $inscription->setSession($session);
        $inscription->setEtatEvaluation($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 27]));

        return $this->renderForm('hopitalnumerique_module_addinscription', $inscription, 'HopitalNumeriqueModuleBundle:Back/Inscription:edit.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de Inscriptions.
     *
     * @param int $id id de Inscriptions
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function editAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        return $this->renderForm('hopitalnumerique_module_addinscription', $inscription, 'HopitalNumeriqueModuleBundle:Back/Inscription:edit.html.twig');
    }

    /**
     * Suppresion d'un Inscriptions.
     *
     * @param int $id Id de Inscription.
     *                METHOD = POST|DELETE
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function deleteAction(Request $request, \HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $sessionId = $inscription->getSession()->getId();

        $url = strpos($this->getRequest()->headers->get('referer'), 'inscription/all') ? $this->generateUrl('hopitalnumerique_module_module_allinscription') : $this->generateUrl('hopitalnumerique_module_module_session_inscription', ['id' => $sessionId]);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->delete($inscription);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response('{"success":true, "url" : "' . $url . '"}', 200);
    }

    /**
     * Passe l'état de l'inscription à 'acceptée'.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function accepterInscriptionAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $refRefuse = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => SessionStatus::STATUT_FORMATION_ACCEPTED_ID]);

        $inscription->setEtatInscription($refRefuse);

        if ($inscription->getSession()->getModule()->getMailConfirmationInscription()) {
            //Envoyer mail d'acceptation de l'inscription
            $mail = $this->get('nodevo_mail.manager.mail')->sendAcceptationInscriptionMail($inscription->getUser(), [
                        'date' => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                        'module' => $inscription->getSession()->getModule()->getTitre(),
                    ]);
            $this->get('mailer')->send($mail);
        }

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

        $this->get('session')->getFlashBag()->add('info', 'L\'inscription de ' . $inscription->getUser()->getAppellation() . ' est acceptée.');

        return $this->redirect($this->generateUrl('hopitalnumerique_module_module_session_inscription', ['id' => $inscription->getSession()->getId()]));
    }

    /**
     * Passe l'état de l'inscription à 'refusée'.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function refuserInscriptionAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $refRefuse = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => SessionStatus::STATUT_FORMATION_REFUSED_ID]);

        $inscription->setEtatInscription($refRefuse);

        if ($inscription->getSession()->getModule()->getMailRefusInscription()) {
            //Envoyer mail de refus de l'inscription
            $mail = $this->get('nodevo_mail.manager.mail')->sendRefusInscriptionMail($inscription->getUser(), [
                        'date' => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                        'module' => $inscription->getSession()->getModule()->getTitre(),
                    ]);
            $this->get('mailer')->send($mail);
        }

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

        $this->get('session')->getFlashBag()->add('info', 'L\'inscription de ' . $inscription->getUser()->getAppellation() . ' est refusée.');

        return $this->redirect($this->generateUrl('hopitalnumerique_module_module_session_inscription', ['id' => $inscription->getSession()->getId()]));
    }

    /**
     * Passe l'état de l'inscription à 'annulée'.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function annulerInscriptionAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $refAnnule = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => SessionStatus::STATUT_FORMATION_CANCELED_ID]);

        $inscription->setEtatInscription($refAnnule);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

        $this->get('session')->getFlashBag()->add('info', 'L\'inscription de ' . $inscription->getUser()->getAppellation() . ' est annulée.');

        return $this->redirect($this->generateUrl('hopitalnumerique_module_module_session_inscription', ['id' => $inscription->getSession()->getId()]));
    }

    /**
     * Effectue le render du formulaire Inscritpion.
     *
     * @param string      $formName    Nom du service associé au formulaire
     * @param Inscritpion $inscritpion Entité $session
     * @param string      $view        Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    private function renderForm($formName, $inscription, $view)
    {
        //On passe un tableau des id de roles autorisé pour pouvoir filtrer les utilisateurs dans le formulaire
        $roleNames = [];
        foreach ($inscription->getSession()->getRestrictionAcces() as $role) {
            $roleNames[] = $role->getRole();
        }

        $label_attr = [
            'roleNames' => $roleNames,
        ];

        //Création du formulaire via le service
        $form = $this->createForm($formName, $inscription, [
            'label_attr' => $label_attr,
        ]);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($inscription->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add(($new ? 'success' : 'info'), 'Inscription ' . ($new ? 'ajoutée.' : 'mise à jour.'));

                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');

                return $this->redirect(($do == 'save-close' ? $this->generateUrl('hopitalnumerique_module_module_session_inscription', ['id' => $inscription->getSession()->getId()]) : $this->generateUrl('hopitalnumerique_module_module_session_inscription_edit', ['id' => $inscription->getId()])));
            }
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'inscription' => $inscription,
        ]);
    }
}
