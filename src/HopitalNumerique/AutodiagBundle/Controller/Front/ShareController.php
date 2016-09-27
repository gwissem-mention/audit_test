<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\ShareType;
use HopitalNumerique\AutodiagBundle\Service\Share;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ShareController extends Controller
{
    /**
     * @param Request $request
     * @param $synthesis
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, Synthesis $synthesis, Domaine $domain = null)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();

        /** @var User $user */
        $user = $this->getUser();
        $domainesUser = $user->getDomaines();

        if (!$this->isGranted('share', $synthesis)) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId()
            ]);
        }

        // Redirection si calcul en cours
        if ($synthesis->isComputing()) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        $form = $this->createForm(ShareType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $share = $this->get('autodiag.synthesis.share');
            $notFounds = $share->shareFromString($synthesis, $form->get('shares')->getData());

            if (!empty($notFounds)) {
                $this->addFlash(
                    'danger',
                    sprintf('Email(s) non trouvé(s) sur la plateforme : %s', implode(', ', $notFounds))
                );
            }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('hopitalnumerique_autodiag_share_index', [
                'synthesis' => $synthesis->getId()
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Account/partials:autodiag_list.html.twig', array(
                'datasForSyntheses' =>
                    $this->get('autodiag.synthesis.dataformatter')->getSynthesesByAutodiag($user, $autodiag, $domain),
                'user' => $user,
                'in_progress' => false,
            ));
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Share:index.html.twig', [
            'synthesis' => $synthesis,
            'form' => $form->createView(),
            'datasForSyntheses' =>
                $this->get('autodiag.synthesis.dataformatter')->getSynthesesByAutodiag($user, $autodiag, $domain),
            'user' => $user,
            'in_progress' => false,
            'domainesUser' => $domainesUser,
            'currentDomain' => $domain,
        ]);
    }

    /**
     * @param Synthesis $synthesis
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
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
