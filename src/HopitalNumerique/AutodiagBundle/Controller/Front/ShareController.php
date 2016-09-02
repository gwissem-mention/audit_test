<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShareController extends Controller
{
    public function indexAction($synthesis)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

//
//        $user = $this->get('hopitalnumerique_user.manager.user')->findOneById(209);
//        $synthesis->addShare($user);


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

        return $this->render('HopitalNumeriqueAutodiagBundle:Share:index.html.twig', [
            'synthesis' => $synthesis,
        ]);
    }
}
