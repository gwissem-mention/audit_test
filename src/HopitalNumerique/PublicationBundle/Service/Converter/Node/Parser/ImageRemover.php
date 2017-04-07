<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use Symfony\Component\DomCrawler\Crawler;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use HopitalNumerique\PublicationBundle\Service\Converter\MediaUploader;

/**
 * Remove excluded media and their HTML tag
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class ImageRemover implements ParserInterface
{
    /**
     * @var MediaUploader
     */
    protected $mediaUploader;

    public function setMediaUploader(MediaUploader $mediaUploader)
    {
        $this->mediaUploader = $mediaUploader;
    }

    public function parse(Node $node)
    {
        $medias = $node->getExcludedMedias();

        if (!empty($medias)) {
            $crawler = new Crawler("<meta charset=\"UTF-8\">{$node->getContent()}");

            foreach ($medias as $media) {

                $this->mediaUploader->removeMedia($media);

                $imgCrawler = $crawler->filter('img[src="' . $media->getOriginalPath() . '"]');
                $imgCrawler->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
            }

            if ($crawler->filter('body')->count() > 0) {
                $node->setContent($crawler->filter('body')->html());
            }
        }

        return $node;
    }
}
