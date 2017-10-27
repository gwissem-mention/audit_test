<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Repository\Member\ViewedMemberRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\HttpFoundation\JsonResponse;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Service\ViewMember;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Contrôleur des utilisateurs.
 */
class UserController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les membres de la communauté.
     */
    public function listAction(Request $request, $page = 1, $membreId = null)
    {
        $rechercheForm = $this->createForm('hopitalnumerique_communautepratiquebundle_user_recherche');

        if ($request->request->has('resetFiltres')) {
            $this->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
                ->removeFiltres()
            ;

            return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_user_list'));
        } else {
            $this->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
                ->setFiltres($request)
            ;
            $rechercheForm->handleRequest($request);
        }

        /** @var Domaine $domaine */
        $domaine = $this->get(SelectedDomainStorage::class)->getSelectedDomain();

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:list.html.twig', [
            'rechercheForm' => $rechercheForm->createView(),
            'pagerFantaMembres' => $this->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')->getPagerfantaUsers($page, $domaine, ($membreId) ? $membreId : null),
            'membersViewed' => $this->getUser() ? $this->get(ViewedMemberRepository::class)->findByViewer($this->getUser()) : [],
        ]);
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userDetailsAction(User $user, Groupe $group = null)
    {
        if ($this->getUser()) {
            $this->get(ViewMember::class)->viewMember($user, $this->getUser());
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:details.html.twig', [
            'group' => $group,
            'user' => $user,
            'memberActivity' => $this->get('hopitalnumerique_user.service.active_member_calculator')->getMemberActivity($user),
        ]);
    }

    /**
     * Affiche tous les membres d'un groupe.
     */
    public function listByGroupeAction(Groupe $groupe, $filtered = false)
    {
        if (!$this->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessGroupe($groupe)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $domaine = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

        $membersQueryBuilder = $this->get('hopitalnumerique_user.repository.user')->getCommunautePratiqueMembresQueryBuilder($groupe, $domaine);
        $membersQueryBuilder
            ->andWhere('groupeInscription.actif = :filter')
            ->setParameter('filter', !$filtered)
        ;

        $members = $membersQueryBuilder->getQuery()->getResult();

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:listByGroupe.html.twig', [
            'groupe' => $groupe,
            'canDeleteMembre' => $this->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteMembre($groupe),
            'members' => $members,
            'membersViewed' => $this->get(ViewedMemberRepository::class)->findByViewer($this->getUser(), $members),
        ]);
    }

    /**
     * @param Request $request
     * @param Groupe $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addUserAction(Request $request, Groupe $group)
    {
        if ($this->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAddMembre($group)) {
            $ajoutMembreForm = $this->createForm(
                'hopitalnumerique_communautepratiquebundle_user_ajout',
                null,
                [
                    'groupe' => $group,
                    'action' => $this->generateUrl('hopitalnumerique_communautepratique_user_add', ['group' => $group->getId()]),
                ]
            );
            $ajoutMembreForm->handleRequest($request);

            if ($ajoutMembreForm->isValid()) {
                $group->addUser($ajoutMembreForm->get('user')->getData());
                $this->get('hopitalnumerique_communautepratique.manager.groupe')->save($group);
                $this->get('session')->getFlashBag()->add('success', 'L\'utilisateur a bien été ajouté au groupe.');

                return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_view', ['groupe' => $group->getId()]));
            }
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * Affiche les informations d'un membre.
     */
    public function viewForGroupeAction(User $user, Groupe $groupe)
    {
        if (!$user->isInscritCommunautePratique() || !$this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessCommunautePratique()
        ) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:viewForGroupe.html.twig', [
            'user' => $user,
            'groupe' => $groupe,
            'questionnaireReponses' => $this->container->get('hopitalnumerique_questionnaire.manager.reponse')
                ->reponsesByQuestionnaireByUser($groupe->getQuestionnaire()->getId(), $user->getId()),
            'documents' => $this->container->get('hopitalnumerique_communautepratique.manager.document')
                ->findBy(['user' => $user, 'groupe' => $groupe]),
            'fichierTypes' => $this->container->get('hopitalnumerique_fichier.manager.fichier_type')->findAll(),
            'fiches' => $this->container->get('hopitalnumerique_communautepratique.manager.fiche')
                ->findBy(['user' => $user, 'groupe' => $groupe]),
        ]);
    }

    /**
     * Désinscrit un membre d'un groupe.
     */
    public function desinscritGroupeAction(Groupe $groupe, User $user)
    {
        if (!$this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteMembre($groupe)
        ) {
            return new JsonResponse(['success' => false]);
        }

        $groupe->removeUser($user);
        $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->save($groupe);
        $this->container->get('session')->getFlashBag()->add('success', 'Membre désinscrit.');

        return new JsonResponse(['success' => true]);
    }

    /**
     * Active/désactive un membre d'un groupe.
     */
    public function activeGroupeAction(Groupe $groupe, User $user)
    {
        if (!$this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteMembre($groupe)
        ) {
            return new JsonResponse(['success' => false]);
        }

        $inscription = $this->container->get('hopitalnumerique_communautepratique.manager.groupe.inscription')
            ->getInscription($groupe, $user)[0]
        ;

        $etat = null;
        if (!$inscription->isActif()) {
            $inscription->setActif(true);

            $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

            $urlGroupe = $currentDomaine->getUrl() . $this->generateUrl('hopitalnumerique_communautepratique_groupe_view', ['groupe' => $groupe->getId()]);
            // Alerte l'utilisateur que son compte est activé
            $this->get('nodevo_mail.manager.mail')->sendAlerteInscriptionValideMail(
                $user->getEmail(),
                $groupe->getTitre(),
                $urlGroupe
            );

            $etat = true;
        } else {
            $inscription->setActif(false);
            $etat = false;
        }
        $this->container->get('hopitalnumerique_communautepratique.manager.groupe.inscription')->save($inscription);
        $this->container->get('session')->getFlashBag()->add('success', ($etat) ? 'Membre Activé.' : 'Membre Désactivé.');

        return new JsonResponse(['success' => true]);
    }
}
