<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk\AddRiskType;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk\ShowRiskType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\IncludeRiskCommand;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\ExcludeRiskCommand;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\AddPrivateRiskCommand;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\RiskAnalysis\EditRiskAnalysisType;

class GuidedSearchRiskController extends Controller
{

    /**
     * @param Request $request
     * @param GuidedSearchStep $guidedSearchStep
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addRiskAction(Request $request, GuidedSearchStep $guidedSearchStep)
    {
        $guidedSearch = $guidedSearchStep->getGuidedSearch();
        $this->denyAccessUnlessGranted('access', $guidedSearch);

        $addPrivateRiskCommand = new AddPrivateRiskCommand($guidedSearch, $this->getUser());
        $form = $this->createForm(AddRiskType::class, $addPrivateRiskCommand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('hopitalnumerique_rechercheparcours.handler.add_private_risk_command')->handle($addPrivateRiskCommand);

            $this->addFlash('success', $this->get('translator')->trans('step.risks.add.notifications.success', [], 'guided_search'));
        } else {
            $this->addFlash('danger', $form->getErrors(true)->current()->getMessage());
        }

        return $this->redirect($this->get('hopitalnumerique_rechercheparcours.helper.step_url_generator')->generate($guidedSearchStep));
    }

    /**
     * @param GuidedSearchStep $guidedSearchStep
     * @param Risk $risk
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(GuidedSearchStep $guidedSearchStep, Risk $risk)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearchStep->getGuidedSearch());

        $this->get('hopitalnumerique_rechercheparcours.handler.exclude_risk_command')->handle(
            new ExcludeRiskCommand($guidedSearchStep, $risk)
        );
        $this->addFlash('success', $this->get('translator')->trans('step.risks.remove.notifications.success', [], 'guided_search'));

        return $this->redirect($this->get('hopitalnumerique_rechercheparcours.helper.step_url_generator')->generate($guidedSearchStep));
    }

    /**
     * @param Request $request
     * @param GuidedSearchStep $guidedSearchStep
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showAction(Request $request, GuidedSearchStep $guidedSearchStep)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearchStep->getGuidedSearch());

        $command = new IncludeRiskCommand($guidedSearchStep);

        $form = $this->createForm(ShowRiskType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('hopitalnumerique_rechercheparcours.handler.show_risk_command')->handle($command);

            $this->addFlash('success', $this->get('translator')->trans('step.risks.removed_risks.notifications.success', [], 'guided_search'));
        } else {
            $this->addFlash('danger', $this->get('translator')->trans('step.risks.removed_risks.notifications.error', [], 'guided_search'));
        }

        return $this->redirect($this->get('hopitalnumerique_rechercheparcours.helper.step_url_generator')->generate($guidedSearchStep));
    }

    /**
     * @param Request $request
     * @param GuidedSearch $guidedSearch
     * @param Risk $risk
     * @param string $stepPath
     *
     * @return JsonResponse
     */
    public function riskAnalysisAction(Request $request, GuidedSearch $guidedSearch, Risk $risk, $stepPath)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearch);

        $step = $this->get('hopitalnumerique_rechercheparcours.repository.guided_search_step')
            ->getByGuidedSearchAndStepPathOrCreate($guidedSearch, $stepPath)
        ;

        /** @var RiskAnalysis|null $riskAnalysis */
        $riskAnalysis = $this->get('hopitalnumerique_rechercheparcours.repository.risk_analysis')->getOrCreate($step, $risk, $this->getUser());

        $form = $this->createForm(EditRiskAnalysisType::class, $riskAnalysis);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse(null, 200);
        }

        return new JsonResponse(null, 418);
    }

    /**
     * @param Request $request
     * @param GuidedSearchStep $guidedSearchStep
     * @param string $type
     *
     * @return Response
     */
    public function exportAction(Request $request, GuidedSearchStep $guidedSearchStep, $type)
    {
        $domain = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

        $risks = $this->get('hopitalnumerique_rechercheparcours.factory.step_risks')->getStepRiskDTO($domain, $guidedSearchStep->getGuidedSearch(), $guidedSearchStep);

        $response = new Response();
        if ($type === 'csv') {
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        } else {
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s.%s"', 'export', $type));
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->prepare($request);
        $response->sendHeaders();

        $this->get(sprintf('hopitalnumerique_rechercheparcours.risk_export_%s', $type))->exportGuidedSearchStepRisks($guidedSearchStep, $risks);

        return $response;
    }
}
