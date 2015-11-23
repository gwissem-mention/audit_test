<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Contrôleur concernant les groupes de la communauté de pratique.
 */
class GroupeController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les groupes disponibles.
     */
    public function listAction(Request $request)
    {
        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($request->getSession()->get('domaineId'));

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:list.html.twig',
            array
            (
                'groupesNonDemarres' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findNonDemarres($domaine),
                'groupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findEnCours($domaine),
                'userGroupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findEnCoursByUser($domaine, $this->getUser())
            )
        );
    }

    /**
     * Visualisation d'un groupe.
     */
    public function viewAction(Groupe $groupe)
    {
        echo '@todo';
        die();
    }

    /**
     * Demande d'inscription d'un membre à un groupe.
     */
    public function inscritAction(Groupe $groupe)
    {
        if ($this->getUser()->hasCommunautePratiqueGroupe($groupe))
        {
            return $this->redirect( $this->generateUrl( 'hopitalnumerique_communautepratique_groupe_view', array( 'groupe' => $groupe->getId() ) ) );
        }
        
        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Groupe:inscrit.html.twig', array(
            'groupe' => $groupe,
            'questionnaireOptions' => array(
                'routeRedirect' => json_encode( array( 'quit' => array( 'route' => 'hopitalnumerique_communautepratique_groupe_validinscription', 'arguments' => array( 'groupe' => $groupe->getId() ) ) ) )
            )
        ));
    }

    /**
     * Valide l'inscription à la soumission du formulaire.
     */
    public function validInscriptionAction(Groupe $groupe)
    {
        $user = $this->getUser();

        if (null !== $user && !$user->hasCommunautePratiqueGroupe($groupe))
        {
            if (count($this->container->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $groupe->getQuestionnaire()->getId(), $user->getId() )) > 0)
            {
                $user->addCommunautePratiqueGroupe($groupe);
                $this->container->get('hopitalnumerique_user.manager.user')->save($user);
                return $this->redirect( $this->generateUrl( 'hopitalnumerique_communautepratique_groupe_view', array( 'groupe' => $groupe->getId() ) ) );
            }
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_list'));
    }
}
