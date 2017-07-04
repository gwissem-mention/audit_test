<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back\Objet;

use HopitalNumerique\ObjetBundle\Entity\Risk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk\LinkRisksToObjectCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk\UnlinkRiskToObjectCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk\ReorderRelatedRisksCommand;

/**
 * Class RelatedRiskController
 */
class RelatedRiskController extends Controller
{
    /**
     * @param Objet $object
     *
     * @return Response
     */
    public function listAction(Objet $object)
    {
        $risks = $this->get('hopitalnumerique_objet.repository.risk')->findAll();

        return $this->render('HopitalNumeriqueObjetBundle:Objet:add_risk.html.twig', [
            'risks' => $risks,
            'object' => $object,
        ]);
    }

    /**
     * @param Request $request
     * @param Objet $object
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Objet $object)
    {
        $selectedRisksId = $request->get('risks');

        $linkRisksToObjectCommand = new LinkRisksToObjectCommand($object, $selectedRisksId);

        $this->get('hopitalnumerique_objet.add_related_risks_handler')->handle($linkRisksToObjectCommand);

        $this->addFlash('success', $this->get('translator')->trans('notification.link.success', [], 'related_risk'));

        return $this->redirectToRoute('hopitalnumerique_objet_objet_edit', ['id' => $object->getId()]);
    }

    /**
     * @param Objet $object
     * @param Risk $risk
     *
     * @return Response
     */
    public function unlinkAction(Objet $object, Risk $risk)
    {
        $unlinkBoardToObjectCommand = new UnlinkRiskToObjectCommand($object, $risk);

        $this->get('hopitalnumerique_objet.unlink_risk_to_object_handler')->handle($unlinkBoardToObjectCommand);

        $this->addFlash('info', $this->get('translator')->trans('notification.unlink.success', [], 'related_risk'));

        return $this->redirectToRoute('hopitalnumerique_objet_objet_edit', ['id' => $object->getId()]);
    }

    /**
     * @param Objet $object
     *
     * @return Response
     */
    public function reorderAction(Objet $object)
    {
        $risks = $this->get('request')->request->get('risks');

        $reorderRelatedBoardsCommand = new ReorderRelatedRisksCommand($object, $risks);

        $this->get('hopitalnumerique_objet.reorder_related_risks_handler')->handle($reorderRelatedBoardsCommand);

        return new JsonResponse();
    }
}
