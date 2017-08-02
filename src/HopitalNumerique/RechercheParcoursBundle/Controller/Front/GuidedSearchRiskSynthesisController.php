<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller\Front;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk\SendSynthesisType;

/**
 * Class GuidedSearchRiskSynthesisController
 */
class GuidedSearchRiskSynthesisController extends Controller
{
    /**
     * @param Request $request
     * @param GuidedSearch $guidedSearch
     *
     * @return Response
     */
    public function synthesisAction(Request $request, GuidedSearch $guidedSearch)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearch);

        if (null === $this->getUser()) {
            $request->getSession()->set('urlToRedirect', $request->getUri());

            return $this->redirectToRoute('account_login');
        }
        
        $riskSynthesis = $this->get('hopitalnumerique_rechercheparcours.factory.risk_synthesis')->buildRiskSynthesis($guidedSearch, $this->getUser());

        $sendSynthesisCommand = $this->get('hopitalnumerique_rechercheparcours.factory.send_synthesis_command')->buildCommand($guidedSearch, $this->getUser());
        $sendSynthesisForm = $this->createForm(SendSynthesisType::class, $sendSynthesisCommand);

        $sendSynthesisForm->handleRequest($request);
        if ($sendSynthesisForm->isSubmitted() && $sendSynthesisForm->isValid()) {
            $this->get('hopitalnumerique_rechercheparcours.handler.send_synthesis_command')->handle($sendSynthesisCommand);

            $this->addFlash('success', $this->get('translator')->trans('step.synthesis.actions.mail.notifications.success', [], 'guided_search'));

            return $this->redirectToRoute('hopital_numerique_guided_search_risk_synthesis', ['guidedSearch' => $guidedSearch->getId()]);
        }

        return $this->render('@HopitalNumeriqueRechercheParcours/RechercheParcours/front/synthesis.html.twig', [
            'riskSynthesis' => $riskSynthesis,
            'guidedSearch' => $guidedSearch,
            'sendSynthesisForm' => $sendSynthesisForm->createView(),
            'guidedSearchLink' => $this->get('hopitalnumerique_rechercheparcours.helper.step_url_generator')->generate($guidedSearch->getSteps()->first()),
        ]);
    }

    /**
     * @param GuidedSearch $guidedSearch
     *
     * @return Response
     */
    public function PDFExportAction(GuidedSearch $guidedSearch)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearch);

        $response = new Response($this->get('hopitalnumeriquerechercheparcours.export.pdf.risk_synthesis')->generatePDF($guidedSearch, $this->getUser()));
        $response->headers->set('Content-Disposition', 'attachment; filename="export.pdf";');
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * @param Request $request
     * @param GuidedSearch $guidedSearch
     * @param string $type
     *
     * @return Response
     */
    public function exportAction(Request $request, GuidedSearch $guidedSearch, $type = 'csv')
    {
        $filepath = stream_get_meta_data(tmpfile())['uri'];

        $this->get(sprintf('hopitalnumerique_rechercheparcours.synthesis_export_%s', $type))->exportGuidedSearch($guidedSearch, $this->getUser(), $filepath);

        $response = new BinaryFileResponse($filepath);
        if ($type === 'csv') {
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        } else {
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s.%s"', 'export', $type));
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
