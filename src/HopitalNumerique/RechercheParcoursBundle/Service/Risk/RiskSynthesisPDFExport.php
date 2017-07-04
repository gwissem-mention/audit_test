<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk;

use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\UserBundle\Entity\User;
use Knp\Snappy\GeneratorInterface;

class RiskSynthesisPDFExport
{
    /**
     * @var RiskSynthesisFactory $riskSynthesisFactory
     */
    protected $riskSynthesisFactory;

    /**
     * @var GeneratorInterface $pdfGenerator
     */
    protected $pdfGenerator;

    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * RiskSynthesisPDFExport constructor.
     *
     * @param RiskSynthesisFactory $riskSynthesisFactory
     * @param GeneratorInterface $pdfGenerator
     * @param \Twig_Environment $twig
     */
    public function __construct(RiskSynthesisFactory $riskSynthesisFactory, GeneratorInterface $pdfGenerator, \Twig_Environment $twig)
    {
        $this->riskSynthesisFactory = $riskSynthesisFactory;
        $this->pdfGenerator = $pdfGenerator;
        $this->twig = $twig;
    }

    /**
     * Return the PDF content
     *
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     *
     * @return string
     */
    public function generatePDF(GuidedSearch $guidedSearch, User $user = null)
    {
        $riskSynthesis = $this->riskSynthesisFactory->buildRiskSynthesis($guidedSearch, $user);

        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->twig->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:front/synthesis.html2pdf.twig', [
                'riskSynthesis' => $riskSynthesis,
                'guidedSearch' => $guidedSearch,
            ]),
            [
                'encoding' => 'UTF-8',
                'footer-html' => $this->twig->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:front/synthesis/synthesis_footer.html2pdf.twig'),
            ]
        );

        return $pdf;
    }

    /**
     * Same as generatePDF function but save the file in a temp file (used to send it as e-mail attachment)
     *
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     *
     * @return bool|string
     */
    public function generatePDFFile(GuidedSearch $guidedSearch, User $user = null)
    {
        $filename = sprintf('%s.pdf', tempnam(sys_get_temp_dir(), null));

        file_put_contents($filename, $this->generatePDF($guidedSearch, $user));

        return $filename;
    }
}
