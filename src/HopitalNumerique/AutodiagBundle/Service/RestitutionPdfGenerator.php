<?php

namespace HopitalNumerique\AutodiagBundle\Service;

use HopitalNumerique\AutodiagBundle\Entity\Restitution;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Symfony\Bridge\Twig\TwigEngine;

class RestitutionPdfGenerator
{
    /** @var TwigEngine $templating */
    private $templating;

    /** @var LoggableGenerator $generator */
    private $generator;

    /**
     * RestitutionPdfGenerator constructor
     *
     * @param TwigEngine $templating
     * @param LoggableGenerator $generator
     */
    public function __construct(TwigEngine $templating, LoggableGenerator $generator)
    {
        $this->templating = $templating;
        $this->generator = $generator;
    }

    /**
     * @param Synthesis $synthesis
     * @param Restitution $restitution
     * @param array $resultItems
     *
     * @return string
     */
    public function pdfGenerator(Synthesis $synthesis, Restitution $restitution, array $resultItems)
    {
        $html = $this->templating->render('HopitalNumeriqueAutodiagBundle:Restitution:pdf.html.twig', [
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

        return $this->generator->getOutputFromHtml($html, $options);
    }
}
