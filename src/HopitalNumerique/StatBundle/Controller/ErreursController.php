<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ErreursController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Erreurs/bloc.html.twig', []);
    }

    /**
     * @return BinaryFileResponse
     */
    public function exportAction()
    {
        return $this->get('stat.service.url_exporter')->export();
    }
}
