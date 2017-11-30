<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back\Objet;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CoreBundle\Domain\Command\Relation\LinkObjectCommand;
use HopitalNumerique\CoreBundle\Domain\Command\Relation\LinkObjectHandler;

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
        $selectedRisks = $this->get('hopitalnumerique_objet.repository.risk')->findById($request->get('risks'));

        foreach ($selectedRisks as $risk) {
            $this->get(LinkObjectHandler::class)->handle(new LinkObjectCommand($object, $risk));
        }

        $this->addFlash('success', $this->get('translator')->trans('notification.link.success', [], 'related_risk'));

        return $this->redirectToRoute('hopitalnumerique_objet_objet_edit', ['id' => $object->getId()]);
    }
}
