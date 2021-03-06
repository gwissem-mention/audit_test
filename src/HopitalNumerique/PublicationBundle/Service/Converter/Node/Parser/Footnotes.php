<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use HopitalNumerique\PublicationBundle\Service\Converter\MediaUploader;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Amend footnote in node content and reset numbers.
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class Footnotes implements ParserInterface
{
    public static $offset = 1;

    public function parse(Node $node)
    {
        if (empty($node->getFootnotes())) {
            return $node;
        }

        foreach ($node->getFootnotes() as $id => $note) {
            $crawler = new Crawler("<meta charset=\"UTF-8\">{$node->getContent()}");

            $footnotes = $crawler->filter('a[href="#'.$id.'"] sup');

            $footnotes->each(function (Crawler $crawler) {
                $crawler->getNode(0)->nodeValue = self::$offset;
            });

            if ($crawler->filter('body')->count() > 0) {
                $node->setContent($crawler->filter('body')->html());
            }

            $sectionNoteCrawler = $crawler->filter('section.note_bas_de_page ol');

            if (0 === $sectionNoteCrawler->count()) {
                $node->setContent(
                    $node->getContent()
                    . "<section class='note_bas_de_page'><ol start='" . self::$offset . "'><li id='$id'>$note</li></ol></section>"
                );
            } else {
                $node->setContent(
                    str_replace(
                        "</ol></section>",
                        "<li id='$id'>$note</li></ol></section>",
                        $node->getContent()
                    )
                );
            }

            self::$offset++;
        }

        return $node;
    }
}
