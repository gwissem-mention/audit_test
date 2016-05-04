<?php
namespace HopitalNumerique\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ContexteController extends Controller
{
    /**
     * Save contexte.
     */
    public function saveAction(Request $request)
    {
        $referenceIds = $request->request->get('referenceIds', []);
        $response = [
            'success' => true,
            'save' => false
        ];

        if ($this->container->get('hopitalnumerique_account.dependency_injection.doctrine.reference.contexte')->save($referenceIds)) {
            $response['save'] = true;
        }

        return new JsonResponse($response);
    }
}
