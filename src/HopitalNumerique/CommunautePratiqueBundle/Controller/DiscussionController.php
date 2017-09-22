<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

class DiscussionController extends Controller
{
    /**
     * @return Response
     */
    public function publicAction()
    {

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $query = DiscussionListQuery::getPublicDiscussion($domains, [], $this->getUser());


        $discussions = $this->get(DiscussionRepository::class)->getPublicDiscussionsForDomains($query);

        return $this->render('@HopitalNumeriqueCommunautePratique/discussion/public.html.twig', [
            'discussions' => $discussions,
        ]);
    }
}
