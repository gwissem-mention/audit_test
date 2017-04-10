<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Extractor;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;
use HopitalNumerique\PublicationBundle\Service\Converter\MediaUploader;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Pandoc converter.
 * Use pandoc binary file to convert docx files to HTML
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Extractor
 */
class Pandoc implements ConverterInterface
{
    /**
     * @var string
     */
    private $pandocPath;

    /**
     * Media dir
     *
     * @var string
     */
    private $mediaDir;

    /**
     * Technical pandoc data dir
     *
     * @var string|null
     */
    private $dataDir;

    /**
     * Pandoc constructor.
     *
     * @param $pandocPath
     * @param $mediaDir
     */
    public function __construct($pandocPath, $mediaDir, $dataDir)
    {
        $this->pandocPath = $pandocPath;
        $this->mediaDir = $mediaDir;
        $this->dataDir = $dataDir;
    }

    /**
     * Return a Document from a document File
     *
     * @param File $file
     * @return Node
     */
    public function convert(File $file)
    {
        $output = $this->executePandoc($file);

        $crawler = new Crawler($output);

        /** @var Node $currentNode */
        $currentNode = new Node();

        $crawler->filterXPath('//body/*')->each(function (Crawler $domElement) use (&$currentNode) {

            if (in_array($domElement->nodeName(), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                $this->extractImages($currentNode);

                $childNode = new Node($domElement->text());

                while (!$this->isDeepest($domElement->nodeName(), $currentNode->getDeep())) {
                    $currentNode = $currentNode->getParent();
                }

                while ($this->getDeep($domElement->nodeName()) - $currentNode->getDeep() > 1) {
                    $placeholderNode = new Node();
                    $placeholderNode->setParent($currentNode);
                    $currentNode = $placeholderNode;
                }

                $childNode->setParent($currentNode);

                $currentNode = $childNode;

                return;
            }

            $wrapperElement = new \DOMDocument();
            $wrapperElement->appendChild($wrapperElement->importNode($domElement->getNode(0), true));
            $currentNode->appendContent(
                $wrapperElement->saveHTML()
            );
        });
        $this->extractImages($currentNode);


        $root = $currentNode->getHighestParent();

        $this->handleFootnotes($root);

        return $root;
    }

    /**
     * Execute pandoc process
     *
     * @param File $document
     * @return string
     */
    protected function executePandoc(File $document)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($this->pandocPath);

        $processArguments = [
            $document->getRealPath(),
            '--from',
            'docx',
            '--to',
            'html5',
            '--standalone',
            '--extract-media',
            $this->mediaDir,
        ];

        if (null !== $this->dataDir) {
            $processArguments = array_merge($processArguments, [
                '--data-dir',
                $this->dataDir,
            ]);
        }

        $process = $builder
            ->setArguments($processArguments)
            ->getProcess()
        ;

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Determine html h tag deep
     *
     * @param $tag
     * @return int
     */
    private function getDeep($tag)
    {
        return (int) substr($tag, -1);
    }

    /**
     * Determine if a tag is deeper than $deep
     *
     * @param $tag
     * @param $deep
     * @return bool
     */
    private function isDeepest($tag, $deep)
    {
        return $this->getDeep($tag) > $deep;
    }

    /**
     * Rearrange footnotes
     *
     * @param NodeInterface $node
     */
    private function handleFootnotes(NodeInterface $node)
    {
        $footnotes = [];
        $this->extractNodeFootnotes($node, $footnotes);
        $this->injectNodeFootnotes($node, $footnotes);
    }

    private function extractNodeFootnotes(NodeInterface $node, &$footnotes)
    {
        $crawler = new Crawler($node->getContent());
        $sectionItemsCrawler = $crawler->filter('.footnotes ol li');

        $sectionItemsCrawler->each(function (Crawler $crawler, $i) use (&$footnotes) {
            $footnotes[$crawler->attr('id')] = $crawler->html();
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        if ($sectionItemsCrawler->count() > 0) {
            $sectionCrawler = $crawler->filter('section.footnotes');
            foreach ($sectionCrawler as $domNode) {
                if (null !== $domNode->parentNode) {
                    $domNode->parentNode->removeChild($domNode);
                }
            }

            $node->setContent($crawler->filter('body')->html());
        }

        foreach ($node->getChildrens() as $children) {
            $this->extractNodeFootnotes($children, $footnotes);
        }
    }

    private function injectNodeFootnotes(NodeInterface $node, &$footnotes)
    {
        if (empty($footnotes)) {
            return;
        }

        foreach ($footnotes as $id => $note) {
            $crawler = new Crawler("<meta charset=\"UTF-8\">" . $node->getContent());
            $noteCrawler = $crawler->filter('a[href="#' . $id . '"]');

            if ($noteCrawler->count() > 0) {
                $sectionNoteCrawler = $crawler->filter('section.footnotes ol');

                if (0 === $sectionNoteCrawler->count()) {
                    $node->setContent(
                        $node->getContent()
                        . "<section class='note_bas_de_page'><ol><li>$note</li></ol></section>"
                    );
                } else {
                    $node->setContent(
                        str_replace(
                            "</ol></section>",
                            "<li>$note</li></ol></section>",
                            $node->getContent()
                        )
                    );
                }

                unset($footnotes[$id]);
            }
        }

        foreach ($node->getChildrens() as $children) {
            $this->injectNodeFootnotes($children, $footnotes);
        }
    }

    /**
     * Extract node images
     *
     * @param NodeInterface $node
     */
    private function extractImages(NodeInterface $node)
    {
        $crawler = new Crawler($node->getContent());
        $crawler = $crawler->filter('img');
        $crawler->each(function (Crawler $crawler) use ($node) {
            $file = new File($crawler->attr('src'));
            $node->addFile($file);
        });
    }
}
