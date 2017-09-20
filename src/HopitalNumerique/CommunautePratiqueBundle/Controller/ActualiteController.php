<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur des actualités de la communauté de pratique.
 */
class ActualiteController extends Controller
{
    public function indexAction()
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();

        $userRepository = $this->get('hopitalnumerique_user.repository.user');
        $cdpGroupRepository = $this->get('hopitalnumerique_communautepratique.repository.group');

        $domains = $this->getUser()->getDomaines()->filter(function (Domaine $domain) {
            return $domain->getCommunautePratiqueGroupes()->count() > 0;
        })->toArray();

        return $this->render('@HopitalNumeriqueCommunautePratique/Actualite/index.html.twig', [
            'selectedDomain'       => $selectedDomain,
            'availableDomains'     => $domains,
            'runningGroupCount'    => $cdpGroupRepository->countActiveGroups($domains),
            'cdpUserCount'         => $userRepository->countCDPUsers($domains),
            'contributorsCount'    => $userRepository->getCDPUsersInGroupCount($domains),
            'cdpOrganizationCount' => $userRepository->getCDPOrganizationsCount($domains),
            'userRecentGroups'     => $cdpGroupRepository->getUsersRecentGroups($this->getUser(), 4, $domains),
        ]);
    }

    /**
     * Liste des actualités.
     *
     * @param         $page
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction($page, Request $request)
    {
        $categorie = $this->container->get('hopitalnumerique_reference.manager.reference')
            ->findOneById(Reference::ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID);

        return $this->renderList($categorie, $page, $request);
    }

    /**
     * Liste les actualités d'une catégorie.
     *
     * @param Reference $categorie
     * @param           $page
     * @param Request   $request
     *
     * @return RedirectResponse|Response
     */
    public function listByCategorieAction(Reference $categorie, $page, Request $request)
    {
        return $this->renderList($categorie, $page, $request);
    }

    /**
     * Affiche la liste des articles d'une catégorie.
     *
     * @param Reference $categorie Catégorie
     * @param int       $page      Numéro de page
     * @param Request   $request
     *
     * @return RedirectResponse|Response
     */
    private function renderList(Reference $categorie, $page, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessCommunautePratique()) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'));

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

        $actualitesAdapter = new ArrayAdapter($actualites);
        $actualitesPager = new Pagerfanta($actualitesAdapter);
        $actualitesPager->setMaxPerPage(5);
        $actualitesPager->setCurrentPage($page);

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Actualite:list.html.twig', [
            'categorieActualites' => $this->container->get('hopitalnumerique_reference.manager.reference')
                ->findOneById(Reference::ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID),
            'categorie' => $categorie,
            'actualitesPager' => $actualitesPager,
        ]);
    }
}
