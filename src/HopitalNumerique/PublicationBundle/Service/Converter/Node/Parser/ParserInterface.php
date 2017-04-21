<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Node\Parser;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;

interface ParserInterface
{
    public function parse(Node $node);
}
