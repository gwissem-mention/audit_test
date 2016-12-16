<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Event\DataEvent;
use HopitalNumerique\AutodiagBundle\Event\SynthesisEvent;
use HopitalNumerique\AutodiagBundle\EventListener\LogListener;
use HopitalNumerique\AutodiagBundle\Events;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ValidationController extends Controller
{
    /**
     * @param $synthesis
     * @param bool $noLayout
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($synthesis, $noLayout = false)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis)
        ;

        $autodiag = $synthesis->getAutodiag();

        if (!$this->isGranted('read', $synthesis)) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId(),
            ]);
        }

        // Redirection si calcul en cours
        if ($synthesis->isComputing()) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        if ($this->getUser() instanceof User == false) {
            $this->get('session')->set(
                'urlToRedirect',
                $this->generateUrl('hopitalnumerique_autodiag_validation_index', ['synthesis' => $synthesis->getId()])
            );
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Validation:index.html.twig', [
            'synthesis'     => $synthesis,
            'noLayout'      => $noLayout,
        ]);
    }

    /**
     * @param Request $request
     * @param Synthesis $synthesis
     * @param bool $referer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function validateAction(Request $request, Synthesis $synthesis, $referer = false)
    {
        if (!$this->isGranted('validate', $synthesis)) {
            throw new AccessDeniedHttpException();
        }

        $validate = $synthesis->validate($this->getUser());
        if (false === $validate) {
            $this->addFlash('error', $this->get('translator')->trans('ad.validation.error'));

            return $this->redirect($request->headers->get('referer'));
        }

        $dispatcher = $this->get('event_dispatcher');
        $event = new SynthesisEvent($synthesis);
        $dispatcher->dispatch(Events::SYNTHESIS_VALIDATED, $event);

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', $this->get('translator')->trans('ad.validation.success'));

        if (false !== $referer) {
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute(
            true === $request->query->getBoolean('noLayout', false)
                ? 'hopitalnumerique_autodiag_share_index_no_layout'
                : 'hopitalnumerique_autodiag_share_index',
            [
                'synthesis' => $synthesis->getId(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param Synthesis $synthesis
     * @param bool $referer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unvalidateAction(Request $request, Synthesis $synthesis, $referer = false)
    {
        if (!$this->isGranted('validate', $synthesis)) {
            throw new AccessDeniedHttpException();
        }

        $synthesis->unvalidate();
        // On supprime tous les partages si la synthèse est dévalidée
        $synthesis->setShares(new ArrayCollection());
        $this->getDoctrine()->getManager()->flush();

        $this->get('event_dispatcher')->dispatch(
            Events::SYNTHESIS_UNVALIDATED,
            new SynthesisEvent($synthesis)
        )
        ;

        $this->addFlash('success', $this->get('translator')->trans('ad.validation.unvalidate_success'));

        if (false !== $referer) {
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute(
            true === $request->query->getBoolean('noLayout', false)
                ? 'hopitalnumerique_autodiag_entry_edit_no_layout'
                : 'hopitalnumerique_autodiag_entry_edit',
            [
                'entry' => $synthesis->getEntries()->first()->getId(),
            ]
        );
    }
}
