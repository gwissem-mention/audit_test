<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\UserBundle\Entity\User;

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
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessCommunautePratique()) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($request->getSession()->get('domaineId'));

        $groupeUserEnCour = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        ->findEnCoursByUser($domaine, $this->getUser());
        $groupeUserAVenir = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
        ->findNonDemarresByUser($domaine, $this->getUser());
        $groupeUser = array_merge($groupeUserEnCour,$groupeUserAVenir);

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:list.html.twig',
            array
            (
                'groupesNonDemarres' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findNonDemarres($domaine),
                'groupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findEnCours($domaine),
                'userGroupesEnCours' => $groupeUser,
            )
        );
    }

    /**
     * Visualisation d'un groupe.
     */
    public function viewAction(Groupe $groupe)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ("anon." != $user) {

            if ($this->container->get('hopitalnumerique_communautepratique.dependency_injection.inscription')->hasInformationManquante($user)
                || !$user->isInscritCommunautePratique()) {
                $this->container->get('session')->getFlashBag()->add('warning', 'Vous devez rejoindre la communauté de pratique avant de pouvoir rejoindre un groupe.');
                return $this->redirect($this->generateUrl('hopital_numerique_publication_publication_article', [
                    'id' => 1000,
                    'categorie' => "article",
                    'alias' => "la-communaute-de-pratique"
                ]));
            }

            if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessGroupe($groupe)) {
                if (!$user->hasCommunautePratiqueGroupe($groupe)) {
                    return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_inscrit', [
                        'groupe' => $groupe->getId()
                    ]));
                }

                if (!$user->isActifInGroupe($groupe)) {
                    $this->container->get('session')->getFlashBag()->add('success', 'Votre inscription sera activée prochainement par un animateur. Vous recevrez un mail de confirmation.');
                    return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_accueil_index'));
                }
            }
        }
        else {
            return $this->redirect($this->generateUrl('hopital_numerique_publication_publication_article', [
                'id' => 1000,
                'categorie' => "article",
                'alias' => "la-communaute-de-pratique"
            ]));
        }

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:view.html.twig',
            array
            (
                'groupe' => $groupe
            )
        );
    }

    /**
     * Demande d'inscription d'un membre à un groupe.
     */
    public function inscritAction(Groupe $groupe)
    {
        if (null === $this->getUser()) {
            return $this->redirect($this->generateUrl(
                'hopitalnumerique_communautepratique_groupe_view',
                array('groupe' => $groupe->getId())
            ));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Groupe:inscrit.html.twig', array(
            'groupe' => $groupe,
            'questionnaireOptions' => array(
                'routeRedirect' => json_encode(array(
                    'quit' => array(
                        'route' => 'hopitalnumerique_communautepratique_groupe_validinscription',
                        'arguments' => array('groupe' => $groupe->getId())
                    ),
                    'sauvegarde' => array(
                        'route' => 'hopitalnumerique_communautepratique_groupe_validinscription',
                        'arguments' => array('groupe' => $groupe->getId())
                    )
                ))
            )
        ));
    }

    /**
     * Valide l'inscription à la soumission du formulaire.
     */
    public function validInscriptionAction(Groupe $groupe)
    {
        $user = $this->getUser();

        if (null !== $user && !$user->hasCommunautePratiqueGroupe($groupe)) {
            if (count($this->container->get('hopitalnumerique_questionnaire.manager.reponse')
                ->reponsesByQuestionnaireByUser($groupe->getQuestionnaire()->getId(), $user->getId())) > 0) {
                $user->addCommunautePratiqueGroupe($groupe);
                $this->container->get('hopitalnumerique_user.manager.user')->save($user);
                $this->container->get('session')->getFlashBag()->add('success', 'Votre inscription sera activée prochainement par un animateur.');
                // Envoi du mail d'alert pour les animateurs
                $destinataires = array();
                foreach ($groupe->getAnimateurs()->getValues() as $animateur) {
                    $destinataires[$animateur->getNom()] = $animateur->getEmail();
                }
                $this->get('nodevo_mail.manager.mail')->sendAlerteInscriptionMail($destinataires);
            }
        }



        return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_list'));
    }

    /**
     * Contenu de la fenêtre "En savoir plus sur un groupe".
     */
    public function panelInformationsAction(Groupe $groupe)
    {
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:panel_informations.html.twig',
            array
            (
                'groupe' => $groupe
            )
        );
    }

    /**
     * Affiche le contenu de la fenêtre de participation aux groupes d'un utilisateur.
     */
    public function panelUserGroupesAction(User $user, Request $request)
    {
        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'));

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:panel_user_groupes.html.twig',
            array(
                'groupesTermines' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findTerminesByUser($domaine, $user),
                'groupesNonDemarres' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonDemarresByUser($domaine, $user),
                'groupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findEnCoursByUser($domaine, $user)
            )
        );
    }
}
