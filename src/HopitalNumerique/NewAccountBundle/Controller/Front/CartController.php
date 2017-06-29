<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\CartBundle\Form\ReportType;
use HopitalNumerique\CartBundle\Form\SendReportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        return $this->render('NewAccountBundle:cart:cart.html.twig', [
            'cartItems' => $cartItems,
            'reportForm' => $reportForm->createView(),
            'sendReportForm' => $sendReportForm->createView(),
            'reports' => $reports,
            'isStagedReport' => true,
        ]);
    }
}
