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
            ->findOneById(Forum::FORUM_COMMUNAUTE_DE_PRATIQUES_ID);
        $forumLastTopics = $this->container->get('hopitalnumerique_forum.manager.topic')
            ->getLastTopicsForum($forum->getId(), 3);

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Accueil:index.html.twig',
            array(
                'derniereActualite' => $this->container->get('hopitalnumerique_objet.manager.objet')
                    ->getLastArticleForCategorie($this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID), $domaine),
                'groupesEnVedette' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonFermes($domaine, true),
                'userGroupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findEnCoursByUser($domaine, $this->getUser()),
                'totalMembres' => $this->container->get('hopitalnumerique_user.manager.user')
                    ->findCommunautePratiqueMembresCount(),
                'membres' => $this->getMembresAuHasard(),
                'forum' => $forum,
                'forumLastTopics' => $forumLastTopics
            )
        );
    }

    /**
     * Retourne 9 membres au hasard pour le tableau de bord.
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Membres
     */
    private function getMembresAuHasard()
    {
        $messieursAuHasard = $this->container->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(3, $this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::CIVILITE_MONSIEUR_ID))
        ;
        $mesdamesAuHasard = $this->container->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(3, $this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::CIVILITE_MADAME_ID))
        ;

        $membresAuHasard = array_merge(
            $messieursAuHasard,
            $mesdamesAuHasard,
            $this->container->get('hopitalnumerique_user.manager.user')
                ->findCommunautePratiqueRandomMembres(3, null, array_merge($messieursAuHasard, $mesdamesAuHasard))
        );

        shuffle($membresAuHasard);

        return $membresAuHasard;
    }
}
