<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\ShareType;
use HopitalNumerique\AutodiagBundle\Service\Share;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ShareController extends Controller
{
    public function indexAction(Request $request, $synthesis)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);


        $user = $this->get('hopitalnumerique_user.manager.user')->findOneById(209);
        $synthesis->addShare($user);

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

        $form = $this->createForm(ShareType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $share = $this->get('autodiag.synthesis.share');
            $notFounds = $share->shareFromString($synthesis, $form->get('shares')->getData());

            if (!empty($notFounds)) {
                $this->addFlash(
                    'error',
                    sprintf('Les emails suivants n\'ont pas été trouvés : %s', implode(', ', $notFounds))
                );
            }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('hopitalnumerique_autodiag_share_index', [
                'synthesis' => $synthesis->getId()
            ]);
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Share:index.html.twig', [
            'synthesis' => $synthesis,
            'form' => $form->createView(),
        ]);
    }

    public function deleteAction(Synthesis $synthesis, User $user)
    {
        // L'utilisateur doit avoir les droits sur chaque entry de la synthèse
        foreach ($synthesis->getEntries() as $entry) {
            if (!$this->isGranted('edit', $entry)) {
                return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                    'autodiag' => $synthesis->getAutodiag()->getId()
                ]);
            }
        }

        $synthesis->removeShare($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('hopitalnumerique_autodiag_share_index', [
            'synthesis' => $synthesis->getId(),
        ]);
    }
}
