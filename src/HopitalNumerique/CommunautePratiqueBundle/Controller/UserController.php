<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

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

        $this->container->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
            ->setFiltres($request);
        $rechercheForm = $this->createForm('hopitalnumerique_communautepratiquebundle_user_recherche');
        $rechercheForm->handleRequest($request);

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:list.html.twig', array(
            'rechercheForm' => $rechercheForm->createView(),
            'pagerFantaMembres' => $this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')->getPagerfantaUsers($page)
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

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:listByGroupe.html.twig', array(
            'groupe' => $groupe,
            'ajoutMembreForm' => (null !== $ajoutMembreForm ? $ajoutMembreForm->createView() : null),
            'pagerFantaMembres' => $this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')
                ->getPagerfantaUsersByGroupe($groupe, $page)
        ));
    }
}
