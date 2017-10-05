<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Event\Group\GroupRegistrationEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Service\Discussion\NewDiscussionActivityCounter;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Service\Export\Comment\Csv;

/**
 * Contrôleur concernant les groupes de la communauté de pratique.
 */
class GroupeController extends Controller
{
    /**
     * Affiche tous les groupes disponibles.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        if (!$this->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessCommunautePratique()) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $groupeUserEnCour = $this->get('hopitalnumerique_communautepratique.manager.groupe')
        ->findEnCoursByUser($selectedDomain, $this->getUser());
        $groupeUserAVenir = $this->get('hopitalnumerique_communautepratique.manager.groupe')
        ->findNonDemarresByUser($selectedDomain, $this->getUser());
        $groupeUser = array_merge($groupeUserEnCour, $groupeUserAVenir);

        $groups = [];
        if (
            $this->getUser()->getCommunautePratiqueAnimateurGroupes()->count() > 0 ||
            $this->getUser()->hasRoleCDPAdmin()
        ) {
            $groups = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findNonFermes(
                $selectedDomain,
                $this->getUser()->hasRoleCDPAdmin() ? null : $this->getUser()
            );
        }

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:list.html.twig',

            [
                'groupesNonDemarres' => $this->get('hopitalnumerique_communautepratique.manager.groupe')->findNonDemarres($selectedDomain),
                'groupesEnCours' => $this->get('hopitalnumerique_communautepratique.manager.groupe')->findEnCours($selectedDomain),
                'userGroupesEnCours' => $groupeUser,
                'groupes' => $groups,
            ]
        );
    }

    /**
     * Visualisation d'un groupe.
     *
     * @param Request $request
     * @param Groupe  $groupe
     * @param Discussion|null $discussion
     *
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request, Groupe $groupe, Discussion $discussion = null)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $currentDomaine = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

        if ($user instanceof User) {
            $inscription = $this->get('hopitalnumerique_communautepratique.dependency_injection.inscription');

            if ($inscription->hasInformationManquante($user) || !$user->isInscritCommunautePratique()) {
                $this->addFlash(
                    'warning',
                    'Vous devez rejoindre la communauté de pratique avant de pouvoir rejoindre un groupe.'
                );

                $cpArticle = $currentDomaine ? $currentDomaine->getCommunautePratiqueArticle() : null;

                if (null !== $cpArticle) {
                    return $this->redirect(
                        $this->generateUrl('hopital_numerique_publication_publication_article', [
                            'id' => $cpArticle->getId(),
                            'categorie' => 'article',
                            'alias' => $cpArticle->getAlias(),
                        ])
                    );
                }

                return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
            }

            $security = $this->get('hopitalnumerique_communautepratique.dependency_injection.security');
            if (!$security->canAccessGroupe($groupe)) {
                if (!$user->hasCommunautePratiqueGroupe($groupe)) {
                    return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_inscrit', [
                        'groupe' => $groupe->getId(),
                    ]));
                }

                if (!$user->isActifInGroupe($groupe)) {
                    $this->addFlash(
                        'success',
                        'Votre inscription sera activée prochainement par un animateur. Vous recevrez un mail de confirmation.'
                    );

                    return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_list'));
                }
            }
        } else {
            $request->getSession()->set('urlToRedirect', $this->generateUrl('hopitalnumerique_communautepratique_groupe_view', [
                'groupe' => $groupe->getId(),
            ]));

            return $this->redirect(
                $this->generateUrl('account_login')
            );
        }

        $discussionActivityCounter = $this->get(NewDiscussionActivityCounter::class);

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Groupe:view.html.twig', [
            'discussionCounter' => [
                'discussion' => $discussionActivityCounter->getNewDiscussionCount($groupe, $this->getUser()),
                'message' => $discussionActivityCounter->getNewMessageCount($groupe, $this->getUser()),
                'document' => $discussionActivityCounter->getNewDocumentCount($groupe, $this->getUser()),
            ],
            'discussion' => $discussion,
            'groupe' => $groupe,
            'canExportCsv' => $this->container->get(Csv::class)->canExportCsv($user, $groupe)
        ]);
    }

    /**
     * Demande d'inscription d'un membre à un groupe.
     *
     * @param Groupe $groupe
     *
     * @return RedirectResponse|Response
     */
    public function inscritAction(Groupe $groupe)
    {
        if (null === $this->getUser()) {
            return $this->redirect($this->generateUrl(
                'hopitalnumerique_communautepratique_groupe_view',
                ['groupe' => $groupe->getId()]
            ));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Groupe:inscrit.html.twig', [
            'groupe' => $groupe,
            'questionnaireOptions' => [
                'routeRedirect' => json_encode([
                    'quit' => [
                        'route' => 'hopitalnumerique_communautepratique_groupe_validinscription',
                        'arguments' => ['groupe' => $groupe->getId()],
                    ],
                    'sauvegarde' => [
                        'route' => 'hopitalnumerique_communautepratique_groupe_validinscription',
                        'arguments' => ['groupe' => $groupe->getId()],
                    ],
                ]),
            ],
        ]);
    }

    /**
     * Valide l'inscription à la soumission du formulaire.
     *
     * @param Groupe $groupe
     *
     * @return RedirectResponse
     */
    public function validInscriptionAction(Groupe $groupe)
    {
        $user = $this->getUser();

        if (null !== $user && !$user->hasCommunautePratiqueGroupe($groupe)) {
            if (count($answers = $this->get('hopitalnumerique_questionnaire.manager.reponse')
                ->reponsesByQuestionnaireByUser($groupe->getQuestionnaire()->getId(), $user->getId())) > 0
            ) {
                $user->addCommunautePratiqueGroupe($groupe);
                $this->get('hopitalnumerique_user.manager.user')->save($user);
                $this->addFlash('success', 'Votre inscription sera activée prochainement par un animateur.');
                // Envoi du mail d'alert pour les animateurs
                $destinataires = [];

                /** @var User $animateur */
                foreach ($groupe->getAnimateurs()->getValues() as $animateur) {
                    $destinataires[$animateur->getPrenomNom()] = $animateur->getEmail();
                }

                $currentDomaine = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

                $urlGroupe = $currentDomaine->getUrl() . $this->generateUrl(
                    'hopitalnumerique_communautepratique_groupe_view',
                    ['groupe' => $groupe->getId()]
                );

                $this->get('nodevo_mail.manager.mail')->sendAlerteInscriptionMail(
                    $destinataires,
                    $user,
                    $groupe,
                    $urlGroupe
                );

                $this->get('event_dispatcher')->dispatch(Events::GROUP_REGISTRATION, new GroupRegistrationEvent($user, $groupe, $answers));
            }
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_list'));
    }

    /**
     * Contenu de la fenêtre "En savoir plus sur un groupe".
     *
     * @param Groupe $groupe
     *
     * @return Response
     */
    public function panelInformationsAction(Groupe $groupe)
    {
        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Groupe:panel_informations.html.twig', [
            'groupe' => $groupe,
        ]);
    }

    /**
     * Affiche le contenu de la fenêtre de participation aux groupes d'un utilisateur.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return Response
     */
    public function panelUserGroupesAction(User $user, Request $request)
    {
        /** @var Domaine $domaine */
        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'));

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:panel_user_groupes.html.twig',
            [
                'groupesTermines' => $this->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findTerminesByUser($domaine, $user),
                'groupesNonDemarres' => $this->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonDemarresByUser($domaine, $user),
                'groupesEnCours' => $this->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findEnCoursByUser($domaine, $user),
            ]
        );
    }

    /**
     * @param Groupe $group
     *
     * @return StreamedResponse|Response
     */
    public function exportCsvAction(Groupe $group)
    {
        $export = $this->container->get(Csv::class);
        if ($export->canExportCsv($this->getUser(), $group)) {
            $comments = $group->getCommentaires();

            return $export->generateResponse($comments, 'groupe_'.$group->getTitre());
        }

        return new Response(null, 403);
    }
}
