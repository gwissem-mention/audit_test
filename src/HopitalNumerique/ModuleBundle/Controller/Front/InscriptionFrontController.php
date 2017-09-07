<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InscriptionFrontController
 */
class InscriptionFrontController extends Controller
{
    /**
     * @param Request $request
     * @param Session $session
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request, Session $session)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        //Création d'une nouvelle inscription
        $inscription = $this->get('hopitalnumerique_module.manager.inscription')->createEmpty();
        $inscription->setUser($user);
        $inscription->setSession($session);
        $referenceManager = $this->get('hopitalnumerique_reference.manager.reference');
        $inscription->setEtatInscription($referenceManager->findOneBy(['id' => 406]));
        $inscription->setEtatParticipation($referenceManager->findOneBy(['id' => 410]));
        $inscription->setEtatEvaluation($referenceManager->findOneBy(['id' => 27]));

        $form = $this->createForm('hopitalnumerique_module_inscription', $inscription);

        if ($form->handleRequest($request)->isValid()) {
            $refAccepte = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 407]);

            $inscription->setEtatInscription($refAccepte);

            if ($inscription->getSession()->getModule()->getMailConfirmationInscription()) {
                //Envoyer mail d'acceptation de l'inscription
                $mail = $this->get('nodevo_mail.manager.mail')->sendAcceptationInscriptionMail(
                    $inscription->getUser(),
                    [
                        'date'   => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                        'module' => $inscription->getSession()->getModule()->getTitre(),
                    ]
                );
                $this->get('mailer')->send($mail);
            }

            $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas :
            // suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add(('success'), 'Votre inscription a été prise en compte.');

            return $this->redirect(
                $this->generateUrl(
                    'hopitalnumerique_module_session_informations_front',
                    ['id' => $inscription->getSession()->getId()]
                )
            );
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:add.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }

    /**
     * Compte HN : Affiche la liste des inscriptions de l'utilisateur connecté.
     *
     * @return Response
     */
    public function indexAction()
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        //get all inscriptions
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->getInscriptionsForUser($user);

        //get sessions terminées where user connected == formateur
        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsForFormateur($user);

        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:index.html.twig', [
            'inscriptions' => $inscriptions,
            'sessions'     => $sessions,
        ]);
    }

    /**
     * Compte HN : Affiche l'attestation de présence de l'utilisateur connecté.
     *
     * @param Inscription $inscription
     *
     * @return Response
     */
    public function attestationAction(Inscription $inscription)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        $html = $this->renderView('HopitalNumeriqueModuleBundle:Front/Pdf:attestation-presence.html.twig', [
            'inscription' => $inscription,
            'user' => $user,
        ]);

        $options = [
            'margin-bottom' => 10,
            'margin-left' => 4,
            'margin-right' => 4,
            'margin-top' => 10,
            'encoding' => 'UTF-8',
        ];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $options, true),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Attestation_presence.pdf"',
            ]
        );
    }

    /**
     * Compte HN : Télécharge la liste des participants de la session de l'inscription.
     *
     * @param Inscription $inscription
     *
     * @return Response
     */
    public function exportListeParticipantAction(Inscription $inscription)
    {
        $datas = [];

        $session = $inscription->getSession();

        $colonnes = [
            'Nom',
            'Prénom',
            'Région',
            'Établissement',
            'Fonction',
            'Téléphone direct',
            'Téléphone portable',
            'Mail',
        ];

        //Pour chaque session, on parcourt les inscriptions pour les lister
        /** @var Inscription $inscription */
        foreach ($session->getInscriptions() as $inscription) {
            //On prend uniquement les "a participé"
            if ($inscription->getEtatParticipation()->getId() === 411) {
                $row = [];

                /** @var User $user */
                $user = $inscription->getUser();

                $row[0] = $user->getLastname();
                $row[1] = $user->getFirstname();
                $row[2] = $user->getRegion()->getLibelle();
                $row[3] = $user->getOrganization() ? $user->getOrganization()->getNom()
                    : $user->getOrganizationLabel()
                ;
                $row[4] = $user->getJobLabel();
                $row[5] = $user->getPhoneNumber();
                $row[6] = $user->getCellPhoneNumber();
                $row[7] = $user->getEmail();

                $datas[] = $row;
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv(
            $colonnes,
            $datas,
            'export-liste-participant-session.csv',
            $kernelCharset
        );
    }

    /**
     * @param Request     $request
     * @param Inscription $inscription
     * @param bool        $json
     *
     * @return Response
     */
    public function annulationInscriptionAction(Request $request, Inscription $inscription, $json = true)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->getUser();

        if ($user->getId() === $inscription->getUser()->getId()) {
            $inscriptionManager = $this->get('hopitalnumerique_module.manager.inscription');
            $inscriptionManager->toogleEtatInscription(
                [$inscription],
                $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 409])
            );
            $inscriptionManager->toogleEtatParticipation(
                [$inscription],
                $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 412])
            );
            $inscriptionManager->toogleEtatEvaluation(
                [$inscription],
                $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 430])
            );

            $this->addFlash(
                'success',
                'Votre inscription à la session "'
                . $inscription->getSession()->getModule()->getTitre()
                . '" a été annulée.'
            );
        } else {
            $this->addFlash('danger', 'Vous ne pouvez annuler que les inscriptions vous concernant.');
        }

        if (true === $json) {
            return new JsonResponse();
        } else {
            return $this->redirect($request->headers->get('referer'));
        }
    }
}
