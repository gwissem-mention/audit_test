<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\ShareType;
use HopitalNumerique\AutodiagBundle\Form\Type\Synthesis\CompareType;
use HopitalNumerique\AutodiagBundle\Model\Synthesis\CompareCommand;
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
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, Synthesis $synthesis, Domaine $domain = null, $noLayout = false)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();

        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User || !$this->isGranted('share', $synthesis)) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId(),
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
            $em = $this->getDoctrine()->getManager();
            if (!empty($notFounds)) {
                $this->addFlash(
                    'danger',
                    sprintf('Email(s) non trouvé(s) sur la plateforme : %s', implode(', ', $notFounds))
                );
            }

            $em->flush();

            return $this->redirectToRoute(
                $noLayout ? 'hopitalnumerique_autodiag_share_index_no_layout' : 'hopitalnumerique_autodiag_share_index',
                [
                    'synthesis' => $synthesis->getId(),
                ]
            );
        }

        $canCompare = $this->get('autodiag.repository.synthesis')
            ->hasComparableForAutodiag($this->getUser(), $autodiag);

        $comparisonForm = null;
        if ($canCompare) {
            $comparisonForm = $this->createForm(
                CompareType::class,
                new CompareCommand($synthesis),
                [
                    'user' => $this->getUser(),
                    'domaine' => $domain,
                    'autodiag' => $autodiag,
                ]
            );
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Account/partials:autodiag_list.html.twig', [
                'datasForSyntheses' => $this->get('autodiag.synthesis.dataformatter')->getSynthesesByAutodiag($user, $autodiag, $domain),
                'user' => $user,
                'in_progress' => false,
                'comparisonForm' => $comparisonForm ? $comparisonForm->createView() : null,
                'canCompare' => $canCompare,
            ]);
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Share:index.html.twig', [
            'synthesis' => $synthesis,
            'form' => $form->createView(),
            'datasForSyntheses' => $this->get('autodiag.synthesis.dataformatter')->getSynthesesByAutodiag($user, $autodiag, $domain),
            'user' => $user,
            'in_progress' => false,
            'domainesUser' => $user->getDomaines(),
            'currentDomain' => $domain,
            'noLayout' => $noLayout,
            'comparisonForm' => $comparisonForm ? $comparisonForm->createView() : null,
            'canCompare' => $canCompare,
        ]);
    }

    /**
     * @param Synthesis $synthesis
     * @param User      $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Synthesis $synthesis, User $user)
    {
        // L'utilisateur doit avoir les droits de suppression sur la synthèse
        if (!$this->isGranted('delete', $synthesis)) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $synthesis->getAutodiag()->getId(),
            ]);
        }

        $synthesis->removeShare($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('hopitalnumerique_autodiag_share_index', [
            'synthesis' => $synthesis->getId(),
        ]);
    }
}
