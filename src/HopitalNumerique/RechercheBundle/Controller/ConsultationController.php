<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Consultation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ConsultationController
 */
class ConsultationController extends Controller
{
    /**
     * @param Request      $request
     * @param Consultation $consultation
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Consultation $consultation)
    {
        if ($consultation->getUser()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        try {
            $this->get('hopitalnumerique_objet.manager.consultation')->delete($consultation);
            $this->addFlash(
                'success',
                $this->get('translator')->trans('viewed_objects.delete_message.success', [], 'widget')
            );
        } catch (\Exception $exception) {
            $this->addFlash(
                'danger',
                $this->get('translator')->trans('viewed_objects.delete_message.error', [], 'widget')
            );
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
