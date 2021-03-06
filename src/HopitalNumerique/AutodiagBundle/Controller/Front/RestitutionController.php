<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Nodevo\MailBundle\Entity\Mail;
use Nodevo\MailBundle\Form\Type\RecommandationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RestitutionController
 */
class RestitutionController extends Controller
{
    /**
     * @param      $synthesis
     * @param bool $pdf
     * @param bool $noLayout
     *
     * @return RedirectResponse|Response
     */
    public function indexAction($synthesis, $pdf = false, $noLayout = false)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis)
        ;

        $this->get('hopitalnumerique_reference.doctrine.glossaire.parse')->parseAndSaveEntity($synthesis->getAutodiag());

        $autodiag = $synthesis->getAutodiag();
        $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag, $synthesis);

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
                'autodiag' => $autodiag->getId(),
            ]);
        }

        $calculator = $this->get('autodiag.restitution.calculator');
        $resultItems = $calculator->compute($synthesis);

        if ($pdf) {
            return new Response(
                $this->get('autodiag.restitution.pdf_generator')->pdfGenerator($synthesis, $restitution, $resultItems),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'filename="export.pdf"',
                ]
            );
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:index.html.twig', [
            'synthesis' => $synthesis,
            'restitution' => $restitution,
            'result' => $resultItems,
            'noLayout' => $noLayout,
            'isPublished' => $autodiag->isPublished(),
        ]);
    }

    /**
     * @param Synthesis $synthesis
     * @param Item      $restitutionItem
     * @param           $type
     *
     * @return BinaryFileResponse|RedirectResponse
     */
    public function exportItemAction(Synthesis $synthesis, Item $restitutionItem, $type)
    {
        if (!$this->isGranted('read', $synthesis)) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.restitution.forbidden'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $synthesis->getAutodiag()->getId(),
            ]);
        }

        return $this->get('autodiag.restitution_item.export')->export(
            $synthesis,
            $restitutionItem,
            $this->getUser(),
            $type
        );
    }

    /**
     * @param Request   $request
     * @param Synthesis $synthesis
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
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
            )->getTargetUrl(),
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
            [
                'recommandationForm' => $sendResultForm->createView(),
            ]
        );
    }

    /**
     * Redirect to sign in/up page with back redirection.
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Synthesis $synthesis
     * @param bool                                              $signUp
     *
     * @return RedirectResponse
     */
    public function signInAction(Synthesis $synthesis, $signUp = false)
    {
        $this->get('session')->set(
            'urlToRedirect',
            $this->generateUrl('hopitalnumerique_autodiag_restitution_index', [
                'synthesis' => $synthesis->getId(),
            ])
        );

        if ($signUp) {
            return $this->redirectToRoute('hopital_numerique_user_inscription');
        }

        return $this->redirectToRoute('account_login');
    }
}
