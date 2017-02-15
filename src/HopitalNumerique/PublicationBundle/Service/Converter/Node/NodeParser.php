<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;
use HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser\ParserInterface;

class NodeParser
{
    /**
     * @var ParserInterface[]
     */
    protected $parsers = [];

    /**
     * @var array ParserInterface[]
     */
    protected $sorted = [];

    public function addParser(ParserInterface $parser, $priority = 0)
    {
        $this->parsers[$priority][] = $parser;
    }

    public function parse(NodeInterface $node)
    {
        $this->sortParsers();

        foreach ($this->sorted as $parser) {
            $parser->parse($node);
        }
    }

    private function sortParsers()
    {
        krsort($this->parsers);
        $this->sorted = call_user_func_array('array_merge', $this->parsers);
    }
}
