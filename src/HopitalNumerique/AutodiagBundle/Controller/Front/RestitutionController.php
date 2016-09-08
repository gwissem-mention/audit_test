<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RestitutionController extends Controller
{
    public function indexAction($synthesis, $pdf = false)
    {
        $synthesis = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis')
            ->getFullyLoadedSynthesis($synthesis);

        $autodiag = $synthesis->getAutodiag();

        if (null === $autodiag->getRestitution() || null == $autodiag->getAlgorithm()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:empty.html.twig', [
                'synthesis' => $synthesis,
            ]);
        }

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

        $restitution = $this->get('autodiag.repository.restitution')->getForAutodiag($autodiag);

        $calculator = $this->get('autodiag.restitution.calculator');
        $resultItems = $calculator->compute($synthesis);

        if ($pdf) {
            $html = $this->renderView('HopitalNumeriqueAutodiagBundle:Restitution:pdf.html.twig', [
                'synthesis' => $synthesis,
                'restitution' => $restitution,
                'result' => $resultItems,
            ]);

            $options = array(
                'encoding'         => 'UTF-8',
                'javascript-delay' => 1000,
                'margin-top'       => '15',
                'margin-bottom'    => '25',
                'margin-right'     => '15',
                'margin-left'      => '15',
                'header-spacing'   => '2',
                'header-left'      => date('d/m/Y'),
                'header-right'     => 'Page [page] / [toPage]',
                'header-font-size' => '10',
                'footer-spacing'   => '10',
                'page-width' => '1024px',
                'footer-html'      => '<p style="font-size:10px;text-align:center;color:#999"> &copy; ANAP<br>Ces contenus extraits de l\'ANAP sont diffus&eacute;s gratuitement.<br>Toutefois, leur utilisation ou citation est soumise &agrave; l\'inscription de la mention suivante : "&copy; ANAP"</p>'
            );

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $options, true),
                200,
                array(
                    'Content-Type'        => 'application/pdf',
                )
            );
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Restitution:index.html.twig', [
            'synthesis' => $synthesis,
            'restitution' => $restitution,
            'result' => $resultItems,
        ]);
    }
}
