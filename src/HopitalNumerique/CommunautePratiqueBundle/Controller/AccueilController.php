<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ForumBundle\Entity\Forum;

/**
 * Accueil de la communauté de pratique.
 */
class AccueilController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Accueil de la communauté de pratique.
     */
    public function indexAction(Request $request)
    {
        $allowed = $this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessCommunautePratique();

        if (!$allowed) {
            return $this->redirect(
                $this->get('communautepratique_router')->getUrl()
            );
        }

        /** @var Domaine $domaine */
        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'));
        $groupeUserEnCour = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findEnCoursByUser($domaine, $this->getUser());
        $groupeUserAVenir = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonDemarresByUser($domaine, $this->getUser());
        $groupeUser = array_merge($groupeUserEnCour, $groupeUserAVenir);

        $forum = null;
        $topics = null;
        $forumCategory = $domaine->getCommunautePratiqueForumCategory();
        if ($forumCategory) {
            $forum = $forumCategory->getForum();
            $topics = $this->container->get('hopitalnumerique_forum.manager.topic')
                ->getLastTopicsForumEpingle($forum->getId(), 4, $forumCategory->getId() ?: null);
        }

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
                'forumLastTopics' => $topics,
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
