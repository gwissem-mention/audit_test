<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionDisplayQuery;

class DiscussionController extends Controller
{
    /**
     * @return Response
     */
    public function publicAction()
    {
        $discussionRepository = $this->get(DiscussionRepository::class);

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussions = $discussionRepository->getPublicDiscussionsForDomains(DiscussionListQuery::getPublicDiscussion($domains, [], $this->getUser()));
        $this->getDoctrine()->getManager()->clear();
        $discussion = $discussionRepository->getFirstPublicDiscussion(DiscussionDisplayQuery::getPublicDiscussion(current($discussions), $this->getUser()));

        return $this->render('@HopitalNumeriqueCommunautePratique/discussion/public.html.twig', [
            'discussions' => $discussions,
            'firstDiscussion' => $discussion,
        ]);
    }
}
