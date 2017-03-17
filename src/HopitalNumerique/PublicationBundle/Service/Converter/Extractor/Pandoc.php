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

class Pandoc implements ConverterInterface
{
    /**
     * @var string
     */
    private $pandocPath;

    private $mediaDir;

    /**
     * Pandoc constructor.
     * @param MediaUploader $mediaUploader
     * @param string $pandocPath
     */
    public function __construct($pandocPath, $mediaDir)
    {
        $this->pandocPath = $pandocPath;
        $this->mediaDir = $mediaDir;
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

        $root = $currentNode->getHighestParent();

        $this->handleFootnotes($root);

        return $root;
    }

    protected function executePandoc(File $document)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($this->pandocPath);
        $process = $builder
            ->setArguments([
                $document->getRealPath(),
                '--from',
                'docx',
                '--to',
                'html5',
                '--standalone',
                '--data-dir',
                $this->mediaDir,
                '--extract-media',
                $this->mediaDir,
            ])
            ->getProcess()
        ;


        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    private function getDeep($tag)
    {
        return (int) substr($tag, -1);
    }

    private function isDeepest($tag, $deep)
    {
        return $this->getDeep($tag) > $deep;
    }

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
            $footnotes[$crawler->attr('id')] = $crawler->text();
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
                        . "<section class='footnotes'><ol><li><span class='note_bas_de_page'>$note</span></li></ol></section>"
                    );
                } else {
                    $node->setContent(
                        str_replace(
                            "</ol></section>",
                            "<li><span class='note_bas_de_page'>$note</span></li></ol></section>",
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
