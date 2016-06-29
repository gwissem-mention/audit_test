<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SynthesisController extends Controller
{
    public function saveNewAction(Request $request, Autodiag $autodiag)
    {
        $synthesis = Synthesis::create($autodiag);
        $form = $this->createForm(SynthesisType::class, $synthesis);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->persist($synthesis);
            $this->get('doctrine.orm.entity_manager')->flush();

            return new JsonResponse($synthesis);
        }

        return $this->createAccessDeniedException();
    }
}
