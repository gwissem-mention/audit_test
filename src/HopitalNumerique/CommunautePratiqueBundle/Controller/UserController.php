<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Contrôleur des utilisateurs.
 */
class UserController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les membres de la communauté.
     */
    public function listAction(Request $request, $page = 1)
    {
        if (!$this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessCommunautePratique()
        ) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }
        $rechercheForm = $this->createForm('hopitalnumerique_communautepratiquebundle_user_recherche');

		if ($request->request->has('resetFiltres')) {
			$this->container->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
				->removeFiltres();
			return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_user_list'));
			
		} else {
	        $this->container->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
	            ->setFiltres($request);
	        $rechercheForm->handleRequest($request);
		}
		
		$domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
		->findOneById($request->getSession()->get('domaineId'));

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:list.html.twig', array(
            'rechercheForm' => $rechercheForm->createView(),
            'pagerFantaMembres' => $this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')->getPagerfantaUsers($page),
			'groupesTermines' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        		->findTermines($domaine),
			'groupesNonDemarres' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        		->findNonDemarres($domaine),
			'groupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
				->findEnCours($domaine)
        ));
    }

    /**
     * Affiche tous les membres d'un groupe.
     */
    public function listByGroupeAction(Groupe $groupe, Request $request, $page = 1)
    {
        if (!$this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessGroupe($groupe)
        ) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $ajoutMembreForm = null;
        if ($this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAddMembre($groupe)) {
            $ajoutMembreForm = $this->createForm(
                'hopitalnumerique_communautepratiquebundle_user_ajout',
                null,
                array('groupe' => $groupe)
            );
            $ajoutMembreForm->handleRequest($request);

            if ($ajoutMembreForm->isValid()) {
                $groupe->addUser($ajoutMembreForm->get('user')->getData());
                $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->save($groupe);
                $this->container->get('session')->getFlashBag()->add('success', 'L\'utilisateur a bien été ajouté au groupe.');
                return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_user_listbygroupe', array('groupe' => $groupe->getId())));
            }
        }

        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
        ->findOneById($request->getSession()->get('domaineId'));
        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:listByGroupe.html.twig', array(
            'groupe' => $groupe,
            'canDeleteMembre' => $this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteMembre($groupe),
            'ajoutMembreForm' => (null !== $ajoutMembreForm ? $ajoutMembreForm->createView() : null),
            'pagerFantaMembres' => $this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
                ->getPagerfantaUsersByGroupe($groupe, $page),
			'groupesTermines' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        		->findTermines($domaine),
			'groupesNonDemarres' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        		->findNonDemarres($domaine),
			'groupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        		->findEnCours($domaine)
        ));
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

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:viewForGroupe.html.twig', array(
            'user' => $user,
            'groupe' => $groupe,
            'questionnaireReponses' => $this->container->get('hopitalnumerique_questionnaire.manager.reponse')
                ->reponsesByQuestionnaireByUser($groupe->getQuestionnaire()->getId(), $user->getId()),
            'documents' => $this->container->get('hopitalnumerique_communautepratique.manager.document')
                ->findBy(array('user' => $user, 'groupe' => $groupe)),
            'fichierTypes' => $this->container->get('hopitalnumerique_fichier.manager.fichier_type')->findAll(),
            'fiches' => $this->container->get('hopitalnumerique_communautepratique.manager.fiche')
                ->findBy(array('user' => $user, 'groupe' => $groupe))
        ));
    }

    /**
     * Désinscrit un membre d'un groupe.
     */
    public function desinscritGroupeAction(Groupe $groupe, User $user)
    {
        if (!$this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteMembre($groupe)
        ) {
            return new JsonResponse(array('success' => false));
        }

        $groupe->removeUser($user);
        $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->save($groupe);
        $this->container->get('session')->getFlashBag()->add('success', 'Membre désinscrit.');

        return new JsonResponse(array('success' => true));
    }
}
