<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ForumBundle\Entity\Forum;

/**
 * Accueil de la communauté de pratiques.
 */
class AccueilController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Accueil de la communauté de pratiques.
     */
    public function indexAction(Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessCommunautePratique()) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'));
        $forum = $this->container->get('hopitalnumerique_forum.manager.forum')
            ->findOneById(Forum::FORUM_PUBLIC_ID);
		$groupeUserEnCour = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findEnCoursByUser($domaine, $this->getUser());
		$groupeUserAVenir = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonDemarresByUser($domaine, $this->getUser());
		$groupeUser = array_merge($groupeUserEnCour,$groupeUserAVenir);
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Accueil:index.html.twig',
            array(
                'groupes' => (count($this->getUser()->getCommunautePratiqueAnimateurGroupes()) > 0
                    || $this->getUser()->hasRoleAdmin() || $this->getUser()->hasRoleAdminHn()
                    ? ($this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                        ->findNonFermes($domaine, ($this->getUser()->hasRoleAdmin() || $this->getUser()->hasRoleAdminHn() ? null : $this->getUser())))
                    : array()),
                'actualites' => $this->container->get('hopitalnumerique_objet.manager.objet')
                    ->getArticlesForCategorie($this->container->get('hopitalnumerique_reference.manager.reference')
                        ->findOneById(Reference::ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID), $domaine),
                'groupesEnVedette' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonFermes($domaine, null, true),
                'userGroupesEnCours' => $groupeUser,
                'totalMembres' => $this->container->get('hopitalnumerique_user.manager.user')
                    ->findCommunautePratiqueMembresCount(),
                'membres' => $this->getMembresAuHasard(),
                'forum' => $forum,
                'forumLastTopics' => $this->container->get('hopitalnumerique_forum.manager.topic')
                    ->getLastTopicsForum($forum->getId(), 4)
            )
        );
    }

    /**
     * Retourne 12 membres au hasard pour le tableau de bord.
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Membres
     */
    private function getMembresAuHasard()
    {
        $messieursAuHasard = $this->container->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(
                6,
                $this->container->get('hopitalnumerique_reference.manager.reference')
                    ->findOneById(Reference::CIVILITE_MONSIEUR_ID)
            )
        ;
        $mesdamesAuHasard = $this->container->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(
                6,
                $this->container->get('hopitalnumerique_reference.manager.reference')
                    ->findOneById(Reference::CIVILITE_MADAME_ID)
            )
        ;

        $membresAuHasard = array_merge(
            $messieursAuHasard,
            $mesdamesAuHasard
        );

        shuffle($membresAuHasard);

        return $membresAuHasard;
    }
}
