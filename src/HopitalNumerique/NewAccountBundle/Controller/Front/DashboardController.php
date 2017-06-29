<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\NewAccountBundle\Domain\Command\ReorderDashboardCommand;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    /**
     * @return Response
     */
    public function dashboardAction()
    {
        $widgets = $this->get('new_account.dashboard.widgets_aggregator')->getWidgets('dashboard');

        return $this->render('NewAccountBundle:dashboard:dashboard.html.twig', [
            'widgets' => $widgets,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reorderAction(Request $request)
    {
        $command = new ReorderDashboardCommand($request->request->get('datas'), $this->getUser());

        $this->get('new_account.dashboard.command_handler.reorder')->handle($command);

        return new JsonResponse();
    }
}
