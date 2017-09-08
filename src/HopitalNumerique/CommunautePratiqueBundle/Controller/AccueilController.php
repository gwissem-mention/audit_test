<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accueil de la communauté de pratique.
 */
class AccueilController extends Controller
{
    /**
     * Accueil de la communauté de pratique.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $allowed = $this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessCommunautePratique()
        ;

        if (!$allowed) {
            return $this->redirect(
                $this->get('communautepratique_router')->getUrl()
            );
        }

        /** @var Domaine $domaine */
        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'))
        ;
        $groupeUserEnCour = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
            ->findEnCoursByUser($domaine, $this->getUser())
        ;
        $groupeUserAVenir = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
            ->findNonDemarresByUser($domaine, $this->getUser())
        ;
        $groupeUser = array_merge($groupeUserEnCour, $groupeUserAVenir);

        $topicsByCategories = [];
        $forumCategories = $domaine->getCommunautePratiqueForumCategories();
        if (count($forumCategories) > 0) {
            foreach ($forumCategories as $category) {
                $topicsByCategories[] = [
                    'topics' => $this->container
                        ->get('hopitalnumerique_forum.manager.topic')
                        ->getLastTopicsForumEpingle($category->getForum()->getId(), 4, $category->getId() ?: null),
                    'categoryId' => $category->getId(),
                    'categoryName' => $category->getName(),
                    'forumName' => $category->getForum()->getName(),
                ];
            }
        }

        if ($domaine->getId() === 1) {
            $actualites = $this->get('hopitalnumerique_forum.manager.post')->getFirstPostsFromBoard(
                Board::BOARD_MHN_ID
            );
        } elseif ($domaine->getId() === 7) {
            $actualites = $this->get('hopitalnumerique_forum.manager.post')->getFirstPostsFromBoard(
                Board::BOARD_RSE_ID
            );
        } else {
            $actualites = [];
        }

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Accueil:index.html.twig',
            [
                'groupes' => (count($this->getUser()->getCommunautePratiqueAnimateurGroupes()) > 0
                || $this->getUser()->hasRoleAdmin() || $this->getUser()->hasRoleAdminHn()
                    ? ($this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                        ->findNonFermes($domaine, (
                            $this->getUser()->hasRoleAdmin()
                            || $this->getUser()->hasRoleAdminHn() ? null : $this->getUser()
                        ))
                    ) : []),
                'actualites' => $actualites,
                'groupesEnVedette' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findNonFermes($domaine, null, true),
                'userGroupesEnCours' => $groupeUser,
                'totalMembres' => $this->container->get('hopitalnumerique_user.manager.user')
                    ->findCommunautePratiqueMembresCount(),
                'membres' => $this->getMembresAuHasard(),
                'forumLastTopics' => $this->get('hopitalnumerique_forum.manager.topic')
                    ->formatTopics($topicsByCategories),
            ]
        );
    }

    /**
     * Retourne 12 membres au hasard pour le tableau de bord.
     *
     * @return User[] Membres
     */
    private function getMembresAuHasard()
    {
        $membresAuHasard = $this->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(12)
        ;

        shuffle($membresAuHasard);

        return $membresAuHasard;
    }
}
