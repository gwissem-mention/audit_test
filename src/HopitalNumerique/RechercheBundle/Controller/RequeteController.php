<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheBundle\Form\Type\RequeteType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class RequeteController
 */
class RequeteController extends Controller
{
    /**
     * @param Request $request
     * @param Requete $search
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Requete $search)
    {
        if ($this->getUser()->getId() !== $search->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        try {
            $this->get('doctrine.orm.entity_manager')->remove($search);

            $this->get('doctrine.orm.entity_manager')->flush();
        } catch (\Exception $exception) {
            $this->addFlash('danger', $this->get('translator')->trans('saved_searches.delete.error', [], 'widget'));

            return $this->redirect($request->headers->get('referer'));
        }

        $this->addFlash('success', $this->get('translator')->trans('saved_searches.delete.success', [], 'widget'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param Request   $request
     * @param Requete   $search
     *
     * @return JsonResponse
     */
    public function changeNameAction(Request $request, Requete $search)
    {
        if ($this->getUser()->getId() !== $search->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(RequeteType::class, $search, [
            'csrf_protection' => false,
        ]);

        $form->submit([
            'nom' => $request->request->get('text'),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->flush($search);
        }

        return new JsonResponse();
    }
}
