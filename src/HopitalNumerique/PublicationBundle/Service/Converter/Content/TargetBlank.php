<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Content;

use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\DomCrawler\Crawler;

/**
 * TargetBlank modify Content content to add blank targets to all external links that are not
 * targeting Object an domains
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Content
 */
class TargetBlank
{
    protected $ownUrls;

    /**
     * Parse all Object contents
     *
     * @param Objet $object
     */
    public function parse(Objet $object)
    {
        $ownUrls = [];
        foreach ($object->getDomaines() as $domaine) {
            $ownUrls[] = $domaine->getUrl();
        }

        $this->ownUrls = $ownUrls;

        foreach ($object->getContenus() as $content) {
            $this->parseContent($content);
        }
    }

    /**
     * Parse one Content
     *
     * @param Contenu $content
     */
    protected function parseContent(Contenu $content)
    {
        $crawler = new Crawler("<meta charset=\"UTF-8\">" . $content->getContenu());
        $linkCrawler = $crawler->filter('a');

        $linkCrawler->each(function (Crawler $crawler) {
            $own = false;
            foreach ($this->ownUrls as $ownUrl) {
                if (0 === strpos($crawler->getNode(0)->getAttribute('href'), '#')) {
                    return true;
                }

                if (false !== strpos($crawler->getNode(0)->getAttribute('href'), $ownUrl)) {
                    $own = true;
                }
            }

            if (!$own) {
                $crawler->getNode(0)->setAttribute('target', '_blank');
            }

            return true;
        });

        if ($crawler->filter('body')->count() > 0) {
            $content->setContenu($crawler->filter('body')->html());
        }
    }
}
