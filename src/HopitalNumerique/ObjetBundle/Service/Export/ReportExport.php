<?php

namespace HopitalNumerique\ObjetBundle\Service\Export;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ObjetBundle\Model\Report;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;

class ReportExport
{
    /**
     * @var ObjetRepository $objectRepository
     */
    private $objectRepository;

    /**
     * @var ObjetManager $objectManager
     */
    private $objectManager;

    public function __construct(ObjetRepository $objetRepository, ObjetManager $objetManager)
    {
        $this->objectRepository = $objetRepository;
        $this->objectManager = $objetManager;
    }

    public function export($objectIds, $all, $charset)
    {
        $columns = [
            'domains' => 'Domaine(s)',
            'id' => 'ID objet',
            'label' => 'Libellé de l\'objet',
            'contentId' => 'ID de l\'infradoc',
            'contentLabel' => 'Libellé de l\'infradoc',
            'link' => 'Lien',
            'linkText' => 'Nom sur le lien',
            'imageName' => 'Nom de l\'image',
            'imageAlt' => 'Alt de l\'image',
            'footnote' => 'Note de bas de page',
            'tableCount' => 'Nombre de tableaux',
        ];

        if (true == $all) {
            $objects = $this->objectRepository->findBy(['isArticle' => 0]);
        } else {
            $objects = $this->objectRepository->findByIds($objectIds);
        }

        $allData = [];

        /** @var Objet $object */
        foreach ($objects as $object) {
            $report = new Report();
            $report->buildReport($object);

            $data = [];
            $data = $this->addLinkRows($report, $data);
            $data = $this->addImageRows($report, $data);
            $data = $this->addFootnoteRows($report, $data);
            $data = $this->addTableCountRow($report, $data);

            $allData = array_merge($allData, $data);

            foreach ($report->children as $child) {
                $childData = [];
                $childData = $this->addLinkRows($child, $childData);
                $childData = $this->addImageRows($child, $childData);
                $childData = $this->addFootnoteRows($child, $childData);
                $childData = $this->addTableCountRow($child, $childData);

                $allData = array_merge($allData, $childData);
            }
        }

        return $this->objectManager->exportCsv(
            $columns,
            $allData,
            'rapport_objets.csv',
            $charset
        );
    }

    private function buildRow(Report $report)
    {
        $row = [];
        $row['domains'] = implode(', ', array_map(function (Domaine $domain) {
            return $domain->getNom();
        }, $report->domains->toArray()));
        $row['id'] = $report->objectId;
        $row['label'] = $report->objectLabel;
        $row['contentId'] = $report->contentId;
        $row['contentLabel'] = $report->contentLabel;
        $row['link'] = null;
        $row['linkText'] = null;
        $row['imageName'] = null;
        $row['imageAlt'] = null;
        $row['footnote'] = null;
        $row['tableCount'] = null;

        return $row;
    }

    private function addLinkRows(Report $report, $data)
    {
        foreach ($report->links as $link) {
            $row = $this->buildRow($report);

            $row['link']     = $link['href'];
            $row['linkText'] = $link['text'];

            $data[] = $row;
        }

        return $data;
    }

    private function addImageRows(Report $report, $data)
    {
        foreach ($report->images as $image) {
            $row = $this->buildRow($report);

            $row['imageName'] = $image['name'];
            $row['imageAlt'] = $image['alt'];

            $data[] = $row;
        }

        return $data;
    }

    private function addFootnoteRows(Report $report, $data)
    {
        foreach ($report->footnotes as $footnote) {
            $row = $this->buildRow($report);

            $row['footnote'] = $footnote;

            $data[] = $row;
        }

        return $data;
    }

    private function addTableCountRow(Report $report, $data)
    {
        if ($report->tableCount > 0) {
            $row = $this->buildRow($report);

            $row['tableCount'] = $report->tableCount;

            $data[] = $row;
        }

        return $data;
    }
}
