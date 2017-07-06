<?php

namespace HopitalNumerique\ContextualNavigationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
        return new Response('ok');
    }
}
