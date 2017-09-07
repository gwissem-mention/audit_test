<?php

namespace HopitalNumerique\CartBundle\Controller\Front;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Form\ReportType;
use Symfony\Component\HttpFoundation\JsonResponse;
use HopitalNumerique\CartBundle\Security\ReportVoter;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\CartBundle\Enum\ReportColumnsEnum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use HopitalNumerique\CartBundle\Domain\Command\GenerateReportCommand;
use HopitalNumerique\CartBundle\Domain\Command\RemoveReportFactoryCommand;

/**
 * Class ReportFactoryController
 */
class ReportFactoryController extends Controller
{
    /**
     * @param Request       $request
     * @param ReportFactory $reportFactory
     *
     * @return RedirectResponse
     */
    public function editAction(Request $request, ReportFactory $reportFactory)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $reportFactory->getReport())) {
            throw new AccessDeniedException();
        }

        $command = new GenerateReportCommand($this->getUser(), $reportFactory);
        $form = $this->get('form.factory')->createNamed('report', ReportType::class, $command);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('abort')->isClicked()) {
                $this->get('hopitalnumerique_cart.remove_report_factory_command_handler')->handle(new RemoveReportFactoryCommand($reportFactory));
                $this->addFlash('success', $this->get('translator')->trans('notification.reportAborted', [], 'cart'));
            } else {
                $this->get('hopitalnumerique_cart.generate_report_command_handler')->handle($command);
                $this->addFlash('success', $this->get('translator')->trans('notification.reportGenerated', [], 'cart'));
            }

            return $this->redirectToRoute('account_cart');
        }

        return $this->redirectToRoute('account_cart');
    }

    /**
     * @return JsonResponse
     */
    public function getPendingReportFactoryAction()
    {
        $reportFactory = $this->get('hopitalnumerique_cart.repository.report_factory')->getStagingFactoryForUser($this->getUser());

        if (is_null($reportFactory)) {
            $reportFactory = new ReportFactory($this->getUser());
            $this->getDoctrine()->getManager()->persist($reportFactory);
            $this->getDoctrine()->getManager()->flush();
        }

        $result = $reportFactory->jsonSerialize();

        $result['factoryItems'] = $this->get('hopitalnumerique_cart.builder.item')->buildPendingReport($this->getUser());
        $result['columns'] = ReportColumnsEnum::getReportDefaultColumns();

        return new JsonResponse($result);
    }

    /**
     * @param Report $report
     *
     * @return JsonResponse
     */
    public function getReportFactoryForReportAction(Report $report)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $report)) {
            return new JsonResponse(null, 403);
        }

        $reportFactory = $this->get('hopitalnumerique_cart.repository.report_factory')->findOneBy([
            'owner' => $this->getUser(),
            'report' => $report,
        ]);
        if (is_null($reportFactory)) {
            $reportFactory = $this->get('hopitalnumerique_cart.factory.report_factory')->buildReportFactory($this->getUser(), $report);
            $this->getDoctrine()->getManager()->persist($reportFactory);
            $this->getDoctrine()->getManager()->flush();
        }

        $result = $reportFactory->jsonSerialize();

        $result['factoryItems'] = $this->get('hopitalnumerique_cart.builder.item')->buildForReportFactory($reportFactory);
        $result['columns'] = ReportColumnsEnum::getReportColumns($report);
        $result['isShared'] = $report->getShares()->count() > 0;

        return new JsonResponse($result);
    }
}
