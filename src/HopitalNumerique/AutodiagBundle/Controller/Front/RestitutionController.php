<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Nodevo\MailBundle\Entity\Mail;
use Nodevo\MailBundle\Form\Type\RecommandationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RestitutionController extends Controller
{
    public function indexAction($synthesis, $pdf = false, $noLayout = false)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();
        $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag);

        if (null === $restitution || null == $autodiag->getAlgorithm()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:empty.html.twig', [
                'synthesis' => $synthesis,
                'noLayout' => $noLayout,
            ]);
        }

        // Redirection si calcul en cours
        if ($synthesis->isComputing()) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        if (!$this->isGranted('read', $synthesis)) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.restitution.forbidden'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId()
            ]);
        }

        $calculator = $this->get('autodiag.restitution.calculator');
        $resultItems = $calculator->compute($synthesis);

        if ($pdf) {
            return new Response(
                $this->get('autodiag.restitution.pdf_generator')->pdfGenerator($synthesis, $restitution, $resultItems),
                200,
                array(
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'filename="export.pdf"'
                )
            );
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:index.html.twig', [
            'synthesis' => $synthesis,
            'restitution' => $restitution,
            'result' => $resultItems,
            'noLayout' => $noLayout,
        ]);
    }

    public function exportItemAction(Synthesis $synthesis, Item $restitutionItem, $type)
    {
       if (!$this->isGranted('read', $synthesis)) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.restitution.forbidden'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $synthesis->getAutodiag()->getId()
            ]);
        }

        return $this->get('autodiag.restitution_item.export')->export(
            $synthesis,
            $restitutionItem,
            $this->getUser(),
            $type
        );
    }

    public function sendResultAction(Request $request, Synthesis $synthesis)
    {
        if (!$this->isGranted('read', $synthesis)) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.restitution.forbidden'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        $sendResultMail =
            $this->container->get('nodevo_mail.manager.mail')->findOneById(Mail::MAIL_SHARE_AUTODIAG_RESULT_ID);
        if (null === $sendResultMail) {
            throw new \Exception($this->get('translator')->trans('ad.restitution.mail.not_found'));
        }

        $sendResultForm = $this->createForm(RecommandationType::class, null, [
            'mail' => $sendResultMail,
            'expediteur' => $this->getUser(),
            'url' => $request->headers->get('referer'),
            'action' => $this->redirectToRoute(
                'hopitalnumerique_autodiag_restitution_send_result',
                [
                    'synthesis' => $synthesis->getId(),
                ]
            )->getTargetUrl()
        ]);
        $sendResultForm->handleRequest($request);

        if ($sendResultForm->isSubmitted()) {
            $autodiag = $synthesis->getAutodiag();
            $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag);
            $calculator = $this->get('autodiag.restitution.calculator');
            $resultItems = $calculator->compute($synthesis);

            $this->container->get('nodevo_mail.manager.mail')->sendAutodiagResultMail(
                $sendResultForm->get('expediteur')->getData(),
                $sendResultForm->get('destinataire')->getData(),
                $sendResultForm->get('objet')->getData(),
                $sendResultForm->get('message')->getData(),
                $this->get('autodiag.restitution.pdf_generator')->pdfGenerator($synthesis, $restitution, $resultItems)
            );

            $this->addFlash('success', $this->get('translator')->trans('ad.restitution.mail.success'));
            return $this->redirect($sendResultForm->get('url')->getData());
        }

        return $this->render(
            '@HopitalNumeriqueAutodiag/Restitution/popin.html.twig',
            array(
                'recommandationForm' => $sendResultForm->createView(),
            )
        );
    }
}
