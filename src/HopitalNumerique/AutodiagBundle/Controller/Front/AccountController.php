<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\DataFormer;

class AccountController extends Controller
{
    public function indexAction()
    {
        $currentUser = $this->getUser();

        $domainesId = $currentUser->getDomainesId();

        $dataFormer = $this->get('autodiag.synthesis.dataformer');
        $test = $dataFormer->getSynthesesByAutodiag($currentUser);

        return $this->render('HopitalNumeriqueAutodiagBundle:Account:index.html.twig', array(
            'domainesId' => $domainesId,
            'datasForSyntheses' => $test
        ));
    }
}
