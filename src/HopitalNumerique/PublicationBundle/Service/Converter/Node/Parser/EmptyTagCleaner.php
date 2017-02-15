<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;

/**
 * Remove empty HTML tags
 *
 * @package HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser
 */
class EmptyTagCleaner implements ParserInterface
{
    private $regexp = '/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/';

    public function parse(Node $node)
    {
        $node->setContent(
            preg_replace(
                $this->regexp,
                '',
                $node->getContent()
            )
        );
    }
}
