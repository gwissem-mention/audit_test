<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back;

use HopitalNumerique\ObjetBundle\Domain\Command\DeleteRiskCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\EditRiskCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Form\RiskType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RiskController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $grid = $this->get('hopitalnumerique_objet.grid.risk');

        return $grid->render('HopitalNumeriqueObjetBundle:back:risk\list.html.twig');
    }

    /**
     * @param Request $request
     * @param Risk|null $risk
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Risk $risk = null)
    {
        $riskCommand = EditRiskCommand::createFromRisk($this->getUser(), $risk);;

        $form = $this->createForm(RiskType::class, $riskCommand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $risk = $this->get('hopitalnumerique_objet.handler.edit_risk')->handle($riskCommand);

            $this->addFlash('success', $this->get('translator')->trans('edit.notifications.saved', [], 'risk'));

            if ($request->request->get('do') === 'save-close') {
                return $this->redirectToRoute('hopitalnumerique_objet_risk_list');
            } else {
                return $this->redirectToRoute('hopitalnumerique_objet_risk_edit', ['risk' => $risk->getId()]);
            }
        }

        return $this->render('HopitalNumeriqueObjetBundle:back:risk\edit.html.twig', [
            'form' => $form->createView(),
            'risk' => $risk,
            'domainesCommunsWithUser' => $this
                ->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntityDomainesCommunsWithUser($risk, $this->getUser())
            ,
        ]);
    }

    /**
     * @param Risk $risk
     *
     * @return JsonResponse
     */
    public function deleteAction(Risk $risk)
    {
        if ($success = $this->get('hopitalnumerique_objet.handler.delete_risk')->handle(new DeleteRiskCommand($risk))) {
            $this->addFlash('success', $this->get('translator')->trans('delete.notifications.success', [], 'risk'));
        } else {
            $this->addFlash('danger', $this->get('translator')->trans('delete.notifications.error', [], 'risk'));
        }

        return new JsonResponse([
            'success' => $success,
            'url' => $this->generateUrl('hopitalnumerique_objet_risk_list')
        ]);
    }
}
