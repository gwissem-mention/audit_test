<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;

class SelectedDomainController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function selectAction(Request $request)
    {
        if ($domainId = $request->request->get('selected_domain'))
        {
            $this
                ->get(SelectedDomainStorage::class)
                ->setSelectedDomain(
                    $domainId === SelectedDomainStorage::ALL_DOMAINS_KEYWORD ? null : $domain = $this->get('hopitalnumerique_domaine.repository.domaine')->find($domainId)
                )
            ;

            return new JsonResponse(null, 200);
        }

        return new JsonResponse(null, 418);
    }
}
