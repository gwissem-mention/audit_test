<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Remove empty HTML tags
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class CustomAttributes implements ParserInterface
{
    private $tags = [
        'p' => [
            'style' => 'text-align: justify;',
        ],
        'table' => [
            'class' => 'table table-striped table-bordered table-hover',
        ],
        'footnote' => [
            'class' => 'note_bas_de_page',
        ]
    ];

    public function parse(Node $node)
    {
        $node->setContent(
            $this->customizeAttributes($node->getContent())
        );
    }

    private function customizeAttributes($content)
    {
        if (empty($content)) {
            return $content;
        }

        $crawler = new Crawler("<meta charset=\"UTF-8\">$content");
        foreach ($this->tags as $searchTag => $customAttributes) {
            $tagCrawler = $crawler->filter($searchTag);
            $tagCrawler->each(function (Crawler $crawler) use ($customAttributes) {
                foreach ($customAttributes as $name => $value) {
                    $crawler->getNode(0)->setAttribute($name, $value);
                }
            });
        }

        return $crawler->filter('body')->html();
    }
}
