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
    public function indexAction(Request $request)
    {
        $discussionRepository = $this->get(DiscussionRepository::class);
        $messageRepository = $this->get(MessageRepository::class);
        $userRepository = $this->get('hopitalnumerique_user.repository.user');
        $cdpGroupRepository = $this->get('hopitalnumerique_communautepratique.repository.group');

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $cdpArticle = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()->getCommunautePratiqueArticle();


        return $this->render('@HopitalNumeriqueCommunautePratique/Actualite/index.html.twig', [
            'currentUri' => base64_encode($request->getUri()),
            'publicDiscussionCount' => $discussionRepository->getPublicDiscussionCount($selectedDomain),
            'publicMessageCount' => $messageRepository->getPublicMessageCount($selectedDomain),
            'groupMessageCount' => $messageRepository->getGroupMessageCount($selectedDomain, $this->getUser()),
            'groupMessageFileCount' => $messageRepository->getMessageFileCount($selectedDomain, $this->getUser()),
            'runningGroupCount' => $cdpGroupRepository->countActiveGroups($domains, $this->getUser()),
            'cdpUserCount' => $userRepository->countCDPUsers($domains),
            'contributorsCount' => $userRepository->getCDPContributorCount($domains, $this->getUser()),
            'cdpOrganizationCount' => $userRepository->getCDPOrganizationsCount($domains),
            'userRecentGroups' => $this->getUser() ? $cdpGroupRepository->getUsersRecentGroups($this->getUser(), 4, $domains) : [],
            'wallItems' => $this->get(WallItemRetriever::class)->retrieve($selectedDomain),
            'cdpArticle' => $cdpArticle,
        ]);
    }
}
