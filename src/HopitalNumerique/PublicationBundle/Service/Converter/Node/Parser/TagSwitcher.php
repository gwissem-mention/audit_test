<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;

/**
 * Remove empty HTML tags
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class TagSwitcher implements ParserInterface
{
    private $regexp = '#(</?)([\w]+)([^>]*>)#is';

    private $tags = [
        'b' => 'strong',
    ];

    public function parse(Node $node)
    {
        $node->setContent(
            $this->switchTags($node->getContent())
        );
    }

    private function switchTags($content)
    {
        return preg_replace_callback(
            $this->regexp,
            function ($matches) {
                if (in_array($matches[2], array_keys($this->tags))) {
                    return sprintf('%s%s%s', $matches[1], $this->tags[$matches[2]], $matches[3]);
                }

                return $matches[0];
            },
            $content
        );
    }

}
