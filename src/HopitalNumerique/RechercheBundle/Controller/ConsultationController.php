<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Consultation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ConsultationController
 */
class ConsultationController extends Controller
{
    /**
     * Delete d'une consultation (AJAX).
     *
     * @param int $id ID de la consultation à supprimer
     *
     * @return Response
     */
    public function ajaxDeleteAction($id)
    {
        $consultation = $this->get('hopitalnumerique_objet.manager.consultation')->findOneBy(['id' => $id]);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_objet.manager.consultation')->delete($consultation);
        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl('hopital_numerique_requete_homepage') . '"}',
            200
        );
    }

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
