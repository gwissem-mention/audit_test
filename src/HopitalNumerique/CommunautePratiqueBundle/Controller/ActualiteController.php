<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Service\News\WallItemRetriever;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

/**
 * Contrôleur des actualités de la communauté de pratique.
 */
class ActualiteController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $discussionRepository = $this->get(DiscussionRepository::class);
        $messageRepository = $this->get(MessageRepository::class);
        $userRepository = $this->get('hopitalnumerique_user.repository.user');
        $cdpGroupRepository = $this->get('hopitalnumerique_communautepratique.repository.group');

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $cdpArticle = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()->getCommunautePratiqueArticle();

        return $this->render('@HopitalNumeriqueCommunautePratique/Actualite/index.html.twig', [
            'publicDiscussionCount' => $discussionRepository->getPublicDiscussionCount($selectedDomain),
            'publicMessageCount' => $messageRepository->getPublicMessageCount($selectedDomain),
            'groupMessageCount' => $messageRepository->getGroupMessageCount($selectedDomain),
            'groupMessageFileCount' => $messageRepository->getMessageFileCount($selectedDomain),
            'runningGroupCount' => $cdpGroupRepository->countActiveGroups($domains),
            'cdpUserCount' => $userRepository->countCDPUsers($domains),
            'contributorsCount' => $userRepository->getCDPContributorCount($domains),
            'cdpOrganizationCount' => $userRepository->getCDPOrganizationsCount($domains),
            'userRecentGroups' => $this->getUser() ? $cdpGroupRepository->getUsersRecentGroups($this->getUser(), 4, $domains) : [],
            'wallItems' => $this->get(WallItemRetriever::class)->retrieve($selectedDomain),
            'cdpArticle' => $cdpArticle,
        ]);
    }
}
