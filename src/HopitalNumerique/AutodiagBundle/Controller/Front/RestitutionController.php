<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\RestitutionCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RestitutionController extends Controller
{
    public function indexAction(Synthesis $synthesis)
    {
        $autodiag = $synthesis->getAutodiag();

        // L'utilisateur doit avoir les droits sur chaque entry de la synthèse
        foreach ($synthesis->getEntries() as $entry) {
            if (!$this->isGranted('edit', $entry)) {
                return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                    'autodiag' => $autodiag->getId()
                ]);
            }
        }

        $restitution = $autodiag->getRestitution();

        $calculator = $this->get('autodiag.restitution.calculator');
        $resultItems = $calculator->compute($synthesis);

        return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:index.html.twig', [
            'synthesis' => $synthesis,
            'restitution' => $restitution,
            'result' => $resultItems,
        ]);
    }
}
