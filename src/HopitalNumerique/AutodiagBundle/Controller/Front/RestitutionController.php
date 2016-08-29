<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RestitutionController extends Controller
{
    public function indexAction($synthesis)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();

        if (null === $autodiag->getRestitution()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:empty.html.twig', [
                'synthesis' => $synthesis,
            ]);
        }

        if ($synthesis->getEntries()->count() === 0) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId()
            ]);
        }

        // L'utilisateur doit avoir les droits sur chaque entry de la synthèse
        foreach ($synthesis->getEntries() as $entry) {
            if (!$this->isGranted('edit', $entry)) {
                return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                    'autodiag' => $autodiag->getId()
                ]);
            }
        }

        $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag);

        $calculator = $this->get('autodiag.restitution.calculator');
        $resultItems = $calculator->compute($synthesis);

        return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:index.html.twig', [
            'synthesis' => $synthesis,
            'restitution' => $restitution,
            'result' => $resultItems,
        ]);
    }
}
