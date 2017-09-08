<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ServiceController
 */
class ServiceController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function serviceAction(Request $request)
    {
        $userDomains = $this->getUser()->getDomaines()->toArray();

        $selectedDomainId = $request->request->get('domain');
        $domains = null;

        if (null !== $selectedDomainId && 'all' !== $selectedDomainId) {
            $domains = array_filter($userDomains, function ($domain) use ($selectedDomainId) {
                if ($domain->getId() === (int) $selectedDomainId) {
                    return true;
                }

                return false;
            });
        } else {
            $domains = $userDomains;
        }

        $widgets = $this->get('new_account.dashboard.widgets_aggregator')->getWidgets('service', $domains);

        if ($request->isXmlHttpRequest()) {
            return $this->render('NewAccountBundle:service:content.html.twig', [
                'widgets' => $widgets,
            ]);
        }

        return $this->render('NewAccountBundle:service:service.html.twig', [
            'widgets' => $widgets,
            'userDomains' => $userDomains,
            'selectedDomain' => $selectedDomainId,
            'page'    => 'service-page',
        ]);
    }
}
