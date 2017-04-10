<?php

namespace HopitalNumerique\ObjetBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\DomCrawler\Crawler;

class Report
{
    public $objectId;

    public $contentId;

    public $objectLabel;

    public $contentLabel;

    public $domains;

    public $links;

    public $images;

    public $footnotes;

    public $tableCount;

    public $children;

    public function __construct()
    {
        $this->links = [];
        $this->images = [];
        $this->footnotes = [];
        $this->tableCount = 0;
        $this->children = [];
        $this->domains = new ArrayCollection();
    }

    /**
     * Builds the report (and all his children) corresponding to the object.
     *
     * @param Objet $object
     *
     * @return Report
     */
    public function buildReport(Objet $object)
    {
        $this->objectId = $object->getId();
        $this->objectLabel = $object->getTitre();
        $this->domains = $object->getDomaines();

        $this->links = array_merge($this->findLinks($object->getResume()), $this->findLinks($object->getSynthese()));
        $this->images = array_merge($this->findImages($object->getResume()), $this->findImages($object->getSynthese()));
        $this->footnotes = array_merge(
            $this->findFootnotes($object->getResume()),
            $this->findFootnotes($object->getSynthese())
        );
        $this->tableCount = count(
            array_merge($this->findTables($object->getResume()), $this->findTables($object->getSynthese()))
        );

        /** @var Contenu $content */
        foreach ($object->getContenus() as $content) {
            $contentReport = $this->buildContentReport($content);
            $this->children[] = $contentReport;
        }

        return $this;
    }

    /**
     * Builds the report corresponding to the content.
     *
     * @param Contenu $content
     *
     * @return Report
     */
    private function buildContentReport(Contenu $content)
    {
        $contentReport = new self();
        $contentReport->objectId = $content->getObjet()->getId();
        $contentReport->objectLabel = $content->getObjet()->getTitre();
        $contentReport->contentId = $content->getId();
        $contentReport->contentLabel = $content->getTitre();
        $contentReport->domains = $content->getDomaines();

        $contentReport->links = $this->findLinks($content->getContenu());
        $contentReport->images = $this->findImages($content->getContenu());
        $contentReport->footnotes = $this->findFootnotes($content->getContenu());
        $contentReport->tableCount = count($this->findTables($content->getContenu()));

        return $contentReport;
    }

    /**
     * @param $string
     *
     * @return array
     */
    private function findLinks($string)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($string);

        $links = $crawler->filter('a:not([href^="#fn"])')->each(function (Crawler $node) {
            return ['href' => $node->attr('href'), 'text' => $node->text()];
        });

        return $links;
    }

    /**
     * @param $string
     *
     * @return array
     */
    private function findImages($string)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($string);

        $images = $crawler->filter('img')->each(function (Crawler $node) {
            return ['alt' => $node->attr('alt'), 'src' => $node->attr('src')];
        });

        $pattern = '([\w-]+\.(?:jpg|jpeg|png|gif))';

        foreach ($images as &$image) {
            preg_match_all($pattern, $image['src'], $out, PREG_SET_ORDER);

            $image['name'] = '';

            if (count($out) > 0) {
                $image['name'] = $out[0][0];
            }
        }

        return $images;
    }

    /**
     * @param $string
     *
     * @return array
     */
    private function findFootnotes($string)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($string);

        $footnotes = $crawler->filter('.note_bas_de_page li')->each(function (Crawler $node) {
            return $node->text();
        });

        return $footnotes;
    }

    /**
     * @param $string
     *
     * @return array
     */
    private function findTables($string)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($string);

        $tables = $crawler->filter('table')->each(function (Crawler $node) {
            return $node->text();
        });

        return $tables;
    }
}
