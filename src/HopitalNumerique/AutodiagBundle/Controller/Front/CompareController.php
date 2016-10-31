<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\Synthesis\CompareType;
use HopitalNumerique\AutodiagBundle\Model\Synthesis\CompareCommand;
use HopitalNumerique\AutodiagBundle\Service\Compare\CompareRestitutionCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompareController extends Controller
{
    public function indexAction(Compare $compare)
    {
        $comparator = new CompareRestitutionCalculator(
            $this->get('autodiag.restitution.calculator'),
            $this->get('autodiag.repository.restitution')
        );
        $result = $comparator->compute($compare);

        $autodiag = $compare->getSynthesis()->getAutodiag();
        $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag);

        return $this->render('HopitalNumeriqueAutodiagBundle:Compare:index.html.twig', [
            'compare' => $compare,
            'restitution' => $restitution,
            'result' => $result,
            'noLayout' => false,
        ]);
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
