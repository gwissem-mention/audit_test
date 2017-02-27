<?php

namespace HopitalNumerique\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $config = $this->get('hopital_numerique_search.config_factory')->getConfig();

        return $this->render('HopitalNumeriqueSearchBundle:search:index.html.twig', [
            'config' => json_encode($config),
        ]);
    }
}
