<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Service\ItemFactory\ItemFactory;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

class ReportGenerator
{
    /**
     * @var ItemGeneratorInterface[]
     */
    protected $generators = [];

    /**
     * @var ItemFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var string $rootDir
     */
    protected $rootDir;

    /**
     * @var GeneratorInterface $pdfGenerator
     */
    protected $pdfGenerator;

    /**
     * @var CurrentDomaine $currentDomainService
     */
    protected $currentDomainService;

    /**
     * ReportGenerator constructor.
     *
     * @param ItemFactory $itemFactory
     * @param \Twig_Environment $twig
     * @param string $rootDir
     * @param GeneratorInterface $pdfGenerator
     * @param CurrentDomaine $currentDomainService
     */
    public function __construct(
        ItemFactory $itemFactory,
        \Twig_Environment $twig,
        $rootDir,
        GeneratorInterface $pdfGenerator,
        CurrentDomaine $currentDomaine
    ) {
        $this->itemFactory = $itemFactory;
        $this->twig = $twig;
        $this->rootDir = $rootDir;
        $this->pdfGenerator = $pdfGenerator;
        $this->currentDomainService = $currentDomaine;
    }

    /**
     * @param ItemGeneratorInterface $generator
     */
    public function addGenerator(ItemGeneratorInterface $generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * Generate PDF file for given Report
     *
     * @param Report $report
     */
    public function generate(Report $report)
    {
        $items = $this->getItems($report);
        
        $html = $this->twig->render('@HopitalNumeriqueCart/report/generator/reportPDF.html.twig', [
            'report' => $report,
            'items' => $items,
        ]);

        $html = $this->makeImgPathAbsolute($html);

        $fs = new Filesystem();
        $fs->dumpFile(
            $this->getReportFile($report),
            $this->pdfGenerator->getOutputFromHtml($html, [
                'header-html' => $this->twig->render('@HopitalNumeriqueCart/report/generator/report_pdf_header.html.twig', [
                    'report' => $report,
                ]),
                'footer-html' => $this->twig->render('@HopitalNumeriqueCart/report/generator/reportPDFFooter.html.twig'),
                'margin-left' => 5,
                'margin-right' => 5,
            ])
        );
    }

    private function makeImgPathAbsolute($html)
    {
        $regex = '/(<img\s[^>]*?src\s*=\s*[\'"]+)(?!http)([^\'\"]*?)([\'"][^>]*?>+)/i';

        return preg_replace($regex, sprintf('${1}%s${2}${3}', $this->currentDomainService->getUrl()), $html);
    }

    /**
     * Get all items for given Report
     *
     * @param Report $report
     *
     * @return array
     */
    private function getItems(Report $report)
    {
        $items = [];

        $iterator = $report->getItems()->getIterator();
        $iterator->uasort(function (ReportItem $a, ReportItem $b) {
            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });
        $reportItems = new ArrayCollection(iterator_to_array($iterator));

        foreach ($reportItems as $item) {
            if (!is_null($generatedItem = $this->generateItem($item))) {
                $items[] = $generatedItem;
            }
        }

        return $items;
    }

    /**
     * Retrieve item data to build ReportItem object with the right generator
     *
     * @param ReportItem $reportItem
     *
     * @return \HopitalNumerique\CartBundle\Model\Report\ItemInterface
     */
    private function generateItem(ReportItem $reportItem)
    {
        if (is_null($item = $this->itemFactory->build($reportItem))) {
            return null;
        }

        foreach ($this->generators as $generator) {
            if ($generator->support($item->getObject())) {
                return $generator->process($item->getObject(), $reportItem->getReport());
            }
        }

        throw new \LogicException();
    }

    /**
     * Remove report file
     *
     * @param Report $report
     */
    public function removeReport(Report $report)
    {
        $filepath = $this->getReportFile($report);

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Get the absolute storage path of the report
     *
     * @param Report $report
     *
     * @return string
     */
    public function getReportFile(Report $report)
    {
        return sprintf('%s/../files/cart/report-%d.pdf', $this->rootDir, $report->getId());
    }
}
