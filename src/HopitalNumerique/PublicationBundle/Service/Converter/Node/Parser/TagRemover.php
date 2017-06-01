<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;

/**
 * Remove empty HTML tags
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class TagRemover implements ParserInterface
{
    private $regexp = '#</?([\w]+)[^>]*>#is';

    private $tags = ['em', 'hr', 'blockquote', 'span', 'embed'];

    public function parse(Node $node)
    {
        $node->setContent(
            preg_replace_callback(
                $this->regexp,
                function ($matches) {
                    if (in_array($matches[1], $this->tags)) {
                        return '';
                    }

                    return $matches[0];
                },
                $node->getContent()
            )
        );
    }
}
