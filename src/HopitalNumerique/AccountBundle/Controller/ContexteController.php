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
            'save' => false,
            'urlRedirection' => null,
        ];

        if (null !== $this->getUser()) { // Connecté, on modifie le compte avec les éléments du contexte choisis
            if ($this->container->get('hopitalnumerique_account.doctrine.reference.contexte')->save($referenceIds)) {
                $response['save'] = true;
            }
        } else { // Non connecté, on redirige vers la création de compte avec le contexte pré-rempli
            $this->container->get('hopitalnumerique_account.doctrine.reference.contexte')->setWantCreateUserWithContext();
            $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);
            $response['urlRedirection'] = $this->generateUrl('hopital_numerique_user_inscription');
        }

        return new JsonResponse($response);
    }
}
