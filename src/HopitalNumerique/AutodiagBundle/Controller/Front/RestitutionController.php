<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\RestitutionCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RestitutionController extends Controller
{
    public function indexAction(Synthesis $synthesis)
    {
        $restitution = $synthesis->getAutodiag()->getRestitution();

        $calculator = new RestitutionCalculator();
        $resultItems = $calculator->compute($synthesis);

        return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:index.html.twig', [
            'restitution' => $restitution,
            'result' => $resultItems,
        ]);
    }
}
