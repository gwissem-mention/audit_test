<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ValidationController extends Controller
{
    /**
     * @param $synthesis
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($synthesis)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();

        if (!$this->isGranted('read', $synthesis)) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId()
            ]);
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
        if (!$this->isGranted('validate', $synthesis)) {
            throw new AccessDeniedHttpException();
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
        if (!$this->isGranted('validate', $synthesis)) {
            throw new AccessDeniedHttpException();
        }

        $synthesis->unvalidate();
        // On supprime tous les partages si la synthèse est dévalidée
        $synthesis->setShares(new ArrayCollection());
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('hopitalnumerique_autodiag_entry_edit', [
            'entry' => $synthesis->getEntries()->first()->getId(),
        ]);
    }
}
