<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ValidationController extends Controller
{
    public function indexAction($synthesis)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();

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

        return $this->render('HopitalNumeriqueAutodiagBundle:Validation:index.html.twig', [
            'synthesis' => $synthesis,
        ]);
    }

    /**
     * @param Synthesis $synthesis
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function validateAction(Synthesis $synthesis)
    {
        // L'utilisateur doit avoir les droits sur chaque entry de la synthèse
        foreach ($synthesis->getEntries() as $entry) {
            if (!$this->isGranted('edit', $entry)) {
                return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                    'autodiag' => $synthesis->getAutodiag()->getId()
                ]);
            }
        }

        $synthesis->validate();
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('hopitalnumerique_autodiag_validation_index', [
            'synthesis' => $synthesis->getId(),
        ]);
    }

    /**
     * @param Synthesis $synthesis
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unvalidateAction(Synthesis $synthesis)
    {
        // L'utilisateur doit avoir les droits sur chaque entry de la synthèse
        foreach ($synthesis->getEntries() as $entry) {
            if (!$this->isGranted('edit', $entry)) {
                return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                    'autodiag' => $synthesis->getAutodiag()->getId()
                ]);
            }
        }

        $synthesis->unvalidate();
        // On supprime tous les partages si la synthèse est dévalidée
        $synthesis->setShares(new ArrayCollection());
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('hopitalnumerique_autodiag_validation_index', [
            'synthesis' => $synthesis->getId(),
        ]);
    }
}
