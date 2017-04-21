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
    protected $excludedTag = ['td', 'th'];

    private $regexp = '/<(\w+)%s\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/';

    public function parse(Node $node)
    {
        $node->setContent(
            preg_replace(
                sprintf($this->regexp, implode(null, array_map(function($e) {
                    return sprintf('(?!.*%s)', $e);
                }, $this->excludedTag))),
                '',
                $node->getContent()
            )
        );
    }
}
