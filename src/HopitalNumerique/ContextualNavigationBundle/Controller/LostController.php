<?php

namespace HopitalNumerique\ContextualNavigationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ContextualNavigationBundle\Service\LostInformationRetriever;

/**
 * Class LostController
 */
class LostController extends Controller
{
    /**
     * @param integer $entityType
     * @param integer $entityId
     *
     * @return Response
     */
    public function lostAction($entityType, $entityId)
    {
        return $this->render(
            'HopitalNumeriqueContextualNavigationBundle:lost:lost.html.twig',
            $this->get(LostInformationRetriever::class)->getLostInformation($entityType, $entityId)
        );
    }
}
