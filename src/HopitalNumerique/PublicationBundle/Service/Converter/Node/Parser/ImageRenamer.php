<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use HopitalNumerique\PublicationBundle\Service\Converter\MediaUploader;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Remove empty HTML tags
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class ImageRenamer implements ParserInterface
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
        $medias = $node->getMedias();

        if (!empty($medias)) {
            $crawler = new Crawler("<meta charset=\"UTF-8\">{$node->getContent()}");

            foreach ($medias as $media) {
                try {
                    $this->mediaUploader->moveMedia($media);

                    $imgCrawler = $crawler->filter('img[src="' . $media->getOriginalPath() . '"]');
                    $imgCrawler->each(function (Crawler $crawler) use ($media) {
                        $crawler->getNode(0)->setAttribute('src', '/' . $media->getPath());
                        $crawler->getNode(0)->setAttribute('alt', $media->getName());
                    });
                } catch (FileNotFoundException $e) {
                    $imgCrawler = $crawler->filter('img[src="' . $media->getOriginalPath() . '"]');
                    $imgCrawler->clear();
                }
            }

            if ($crawler->filter('body')->count() > 0) {
                $node->setContent($crawler->filter('body')->html());
            }
        }

        return $node;
    }
}
