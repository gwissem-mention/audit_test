<?php

namespace HopitalNumerique\CartBundle\Controller\Front;

use HopitalNumerique\CartBundle\Domain\Command\GenerateReportCommand;
use HopitalNumerique\CartBundle\Domain\Command\ReorderReportFactoryItemsCommand;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\CartBundle\Form\ReportType;
use HopitalNumerique\CartBundle\Security\ReportVoter;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CartBundle\Entity\Report;
use Symfony\Component\HttpFoundation\JsonResponse;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CartBundle\Domain\Command\RemoveItemFromReportCommand;
use HopitalNumerique\CartBundle\Domain\Command\AddCartItemsToReportFactoryCommand;

class ReportItemController extends Controller
{
    /**
     * @param Report $report
     *
     * @return JsonResponse
     */
    public function getItemsAction(Report $report)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $report)) {
            return new JsonResponse(null, 403);
        }
        
        return new JsonResponse($this->get('hopitalnumerique_cart.builder.item')->buildForReport($report));
    }

    /**
     * @param Request $request
     * @param ReportFactory|null $reportFactory
     *
     * @return JsonResponse
     */
    public function addToReportAction(Request $request, ReportFactory $reportFactory = null)
    {
        if (!is_null($reportFactory) && !$this->isGranted(ReportVoter::EDIT, $reportFactory->getReport())) {
            return new JsonResponse(null, 403);
        }

        $command = new AddCartItemsToReportFactoryCommand($reportFactory, $request->request->get('item', []), $this->getUser());

        $this->get('hopitalnumerique_cart.item_to_report_factory_command_handler')->handle($command);

        return new JsonResponse(null, 201);
    }

    /**
     * @param ReportItem $reportItem
     *
     * @return JsonResponse
     */
    public function removeItemAction(ReportItem $reportItem)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $reportItem->getReport())) {
            return new JsonResponse(null, 403);
        }

        $this->get('hopitalnumerique_cart.remove_item_from_report_command_handler')->handle(new RemoveItemFromReportCommand($reportItem, $this->getUser()));

        return new JsonResponse(null, 204);
    }

    /**
     * @param Request $request
     * @param ReportFactory $reportFactory
     *
     * @return JsonResponse
     */
    public function reorderAction(Request $request, ReportFactory $reportFactory)
    {
        if (!$this->isGranted(ReportVoter::EDIT, $reportFactory->getReport())) {
            return new JsonResponse(null, 403);
        }

        $this->get('hopitalnumerique_cart.reorder_report_factory_items_command_handler')->handle(
            new ReorderReportFactoryItemsCommand($this->getUser(), $reportFactory, $request->request->get('itemsOrder'))
        );

        return new JsonResponse(null, 200);
    }
}
