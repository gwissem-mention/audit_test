<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\Synthesis\CompareType;
use HopitalNumerique\AutodiagBundle\Model\Synthesis\CompareCommand;
use HopitalNumerique\AutodiagBundle\Service\Compare\CompareRestitutionCalculator;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompareController extends Controller
{
    public function indexAction(Compare $compare, $pdf = false)
    {
        $autodiag = $compare->getSynthesis()->getAutodiag();
        $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag);

        if (null === $restitution || null == $autodiag->getAlgorithm()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:empty.html.twig', [
                'synthesis' => $compare->getSynthesis(),
            ]);
        }

        if (!$this->isGranted('read', $compare->getSynthesis()) || !$this->isGranted('read', $compare->getReference())) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.restitution.forbidden'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId()
            ]);
        }

        $comparator = new CompareRestitutionCalculator(
            $this->get('autodiag.restitution.calculator'),
            $this->get('autodiag.repository.restitution')
        );
        $result = $comparator->compute($compare);

        if ($pdf) {
            return new Response(
                $this->get('autodiag.restitution.pdf_generator')->comparePdfGenerator($compare, $restitution, $result),
                200,
                ['Content-Type' => 'application/pdf']
            );
        }


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

            $compare = $this->get('autodiag.compare.builder')->build(
                $this->getUser(),
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

    public function formAction(Domaine $domain = null)
    {
        $comparisonForm = $this->createForm(CompareType::class, new CompareCommand(), [
            'user' => $this->getUser(),
            'domaine' => $domain,
        ]);

        return $this->render('HopitalNumeriqueAutodiagBundle:Compare:_form.html.twig', [
            'form' => $comparisonForm->createView(),
        ]);
    }
}
