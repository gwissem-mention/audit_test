<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\Synthesis\CompareType;
use HopitalNumerique\AutodiagBundle\Model\Synthesis\CompareCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompareController extends Controller
{
    public function indexAction(Compare $compare)
    {
        dump($compare);die;


        return new Response('TODO');
    }

    public function createCompareAction(Request $request)
    {
        $response = new Response();

        $compareCommand = new CompareCommand();
        $compareForm = $this->createForm(CompareType::class, $compareCommand, [
            'user' => $this->getUser(),
        ]);

        $compareForm->handleRequest($request);
        if ($compareForm->isSubmitted() && $compareForm->isValid()) {

            $compare = $this->get('autodiag.synthesis.comparison_builder')->build(
                $compareCommand->synthesis,
                $compareCommand->reference
            );

            $this->getDoctrine()->getManager()->persist($compare);
            $this->getDoctrine()->getManager()->flush();

            $path = $this->generateUrl('hopitalnumerique_autodiag_compare_index', [
                'compare' => $compare->getId(),
            ]);

            $response->headers->set('REDIRECT', $path);
            return $response;
        }

        $response->setContent(
            $this->renderView('@HopitalNumeriqueAutodiag/Account/partials/_compare.html.twig', [
                'form' => $compareForm->createView(),
            ])
        );

        return $response;
    }
}
