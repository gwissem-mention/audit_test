<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CompareController extends Controller
{
    public function indexAction(Synthesis $synthesis, Synthesis $reference)
    {
        $intersection = $this->get('autodiag.synthesis.intersection_builder')->build($synthesis, $reference);
        $this->get('autodiag.score_calculator')->deferSynthesisScore($intersection);


        return new Response('TODO');
    }
}
