<?php

namespace HopitalNumerique\CartBundle\Controller\Front;

use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CartBundle\Entity\Report;
use Symfony\Component\HttpFoundation\JsonResponse;
use HopitalNumerique\CartBundle\Form\SendReportType;
use HopitalNumerique\CartBundle\Security\ReportVoter;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\CartBundle\Entity\ReportDownload;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CartBundle\Domain\Command\SendReportCommand;
use HopitalNumerique\CartBundle\Domain\Command\ShareReportCommand;
use HopitalNumerique\CartBundle\Domain\Command\RemoveReportCommand;
use HopitalNumerique\CartBundle\Repository\ReportDownloadRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use HopitalNumerique\CartBundle\Domain\Command\DuplicateReportCommand;
use HopitalNumerique\CartBundle\Domain\Command\RemoveSharingReportCommand;
use HopitalNumerique\CartBundle\Exception\ReportAlreadySharedToUserException;

/**
 * Class ReportController
 */
class ReportController extends Controller
{
    /**
     * @param Report $report
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(Report $report)
    {
        if (!$this->isGranted(ReportVoter::VIEW, $report)) {
            throw new AccessDeniedHttpException();
        }

        /** @var ReportDownload|null $reportDownload */
        $reportDownload = $this->get(ReportDownloadRepository::class)->findOneByReportAndUser($report, $this->getUser());

        if (null === $reportDownload) {
            $report->addDownload(new ReportDownload($report, $this->getUser()));
        } else {
            $reportDownload->updateDownloadDate();
        }

        $this->get('doctrine.orm.entity_manager')->flush();

        $response = new BinaryFileResponse($this->get('hopitalnumerique_cart.report_generator')->getReportFile($report));

        return $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, sprintf('%s.pdf', $report->getSlug()));
    }

    /**
     * @param Request $request
     * @param Report $report
     *
     * @return RedirectResponse
     */
    public function sendReportAction(Request $request, Report $report)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $report)) {
            throw new AccessDeniedHttpException();
        }

        $command = new SendReportCommand($report);
        $form = $this->createForm(SendReportType::class, $command);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('hopitalnumerique_cart.send_report_command_handler')->handle($command);
            $this->addFlash('success', $this->get('translator')->trans('notification.reportSended.success', [], 'cart'));

            return $this->redirectToRoute('account_cart');
        }

        $this->addFlash('danger', $this->get('translator')->trans('notification.reportSended.error', [], 'cart'));

        return $this->redirectToRoute('account_cart');
    }

    /**
     * @param Report $report
     *
     * @return RedirectResponse
     */
    public function removeAction(Report $report)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $report)) {
            throw new AccessDeniedHttpException();
        }

        $this->get('hopitalnumerique_cart.remove_report_command_handler')->handle(new RemoveReportCommand($report, $this->getUser()));
        $this->addFlash('success', $this->get('translator')->trans('notification.reportRemoved', [], 'cart'));

        return $this->redirectToRoute('account_cart');
    }

    /**
     * @param Request $request
     * @param Report $report
     *
     * @return RedirectResponse
     */
    public function duplicateAction(Request $request, Report $report)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $report)) {
            throw new AccessDeniedHttpException();
        }

        $command = new DuplicateReportCommand($report, $this->getUser());
        $command->reportName = $request->request->get('reportName');
        $this->get('hopitalnumerique_cart.duplicate_report_command_handler')->handle($command);

        $this->addFlash('success', $this->get('translator')->trans('notification.reportDuplicated', [], 'cart'));

        return $this->redirectToRoute('account_cart');
    }

    /**
     * @param Request $request
     * @param Report  $report
     * @param string  $type
     *
     * @return RedirectResponse
     */
    public function shareAction(Request $request, Report $report, $type = ShareReportCommand::TYPE_COPY)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $report)) {
            throw new AccessDeniedHttpException();
        }

        $command = new ShareReportCommand($type, $report, $this->getUser());
        $command->targetEmail = $request->request->get('targetEmail');

        try {
            $this->get('hopitalnumerique_cart.share_report_command_handler')->handle($command);
            $this->addFlash('success', $this->get('translator')->trans(sprintf('notification.shareReport.%s.success', $command->type), [], 'cart'));
        } catch (NoResultException $e) {
            $this->addFlash('danger', $this->get('translator')->trans('notification.shareReport.userNotFounded', [], 'cart'));
        } catch (ReportAlreadySharedToUserException $e) {
            $this->addFlash('danger', $this->get('translator')->trans('notification.shareReport.alreadyShared', [], 'cart'));
        }

        return $this->redirectToRoute('account_cart');
    }

    /**
     * @param Report $report
     *
     * @return JsonResponse
     */
    public function sharesDataAction(Report $report)
    {
        $shares = [];
        if ($this->getUser()->getId() === $report->getOwner()->getId()) {
            $shares[ReportSharing::TYPE_SHARE] = [];
        } else {
            $shares[ReportSharing::TYPE_SHARE] = [
                'ownerFullName' => $report->getOwner()->getNomPrenom(),
            ];
        }

        foreach ($report->getShares() as $share) {
            if ($share->getTarget() === $this->getUser()) {
                continue;
            }

            $shares[$share->getType()][] = [
                'id' => $share->getId(),
                'target' => $share->getTarget()->getNomPrenom(),
            ];
        }

        return new JsonResponse($shares);
    }

    /**
     * @param ReportSharing $reportSharing
     *
     * @return RedirectResponse
     */
    public function removeSharingAction(ReportSharing $reportSharing)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $reportSharing->getReport())) {
            throw new AccessDeniedHttpException();
        }

        $command = new RemoveSharingReportCommand($reportSharing);

        $this->get('hopitalnumerique_cart.remove_report_sharing_command_handler')->handle($command);

        return $this->redirectToRoute('account_cart');
    }
}
