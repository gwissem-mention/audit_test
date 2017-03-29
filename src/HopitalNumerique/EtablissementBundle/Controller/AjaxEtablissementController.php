<?php

namespace HopitalNumerique\EtablissementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxEtablissementController
 */
class AjaxEtablissementController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loadEtablissementAction(Request $request)
    {
        $search = $request->get('search');

        $etablissements = $this->getDoctrine()->getRepository('HopitalNumeriqueEtablissementBundle:Etablissement')
            ->findByNameFinessCity($search)
        ;

        return new JsonResponse(["results" => $etablissements]);
    }
}
