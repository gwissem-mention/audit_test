<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use HopitalNumerique\CartBundle\Entity\Report;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\CartBundle\Form\ReportType;
use HopitalNumerique\CartBundle\Form\SendReportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Service\ShareMessageGenerator;
use HopitalNumerique\CartBundle\Domain\Command\GenerateReportCommand;

/**
 * Class CartController
 */
class CartController extends Controller
{
    /**
     * @return Response
     */
    public function cartAction()
    {
        $itemBuilder = $this->get('hopitalnumerique_cart.builder.item');
        $cartItems = $itemBuilder->buildCart($this->getUser());

        $reportForm = $this->get('form.factory')->createNamed(
            'report',
            ReportType::class,
            new GenerateReportCommand($this->getUser())
        );

        $sendReportCommand = $this->get('hopitalnumerique_cart.factory.send_report_command')->build($this->getUser());
        $sendReportForm = $this->createForm(SendReportType::class, $sendReportCommand);

        $reports = $this->get('hopitalnumerique_cart.repository.report')->findAllForUser($this->getUser());

        $reportShareMessages = [];
        $reportStatuses = [];

        /** @var Report $report */
        foreach ($reports as $report) {
            $shares = array_map(function ($share) {
                return $share->getTarget();
            }, $report->getShares()->toArray());

            $shareMessage = $this->get(ShareMessageGenerator::class)->getShareMessage(
                $shares,
                $report->getOwner(),
                $this->getUser()
            );

            $reportShareMessages[$report->getId()] = strlen($shareMessage) > 0 ? $shareMessage : null;

            $reportDownload = $report->getDownloadByUser($this->getUser());

            if (null === $reportDownload) {
                $reportStatuses[$report->getId()] = Report::STATUS_NEW;
            } elseif ($report->getUpdatedAt() > $reportDownload->getDownloadDate()) {
                $reportStatuses[$report->getId()] = Report::STATUS_UPDATED;
            } else {
                $reportStatuses[$report->getId()] = null;
            }
        }

        return $this->render('NewAccountBundle:cart:cart.html.twig', [
            'cartItems' => $cartItems,
            'reportForm' => $reportForm->createView(),
            'sendReportForm' => $sendReportForm->createView(),
            'reports' => $reports,
            'reportShareMessages' => $reportShareMessages,
            'reportStatuses' => $reportStatuses,
            'isStagedReport' => true,
        ]);
    }
}
