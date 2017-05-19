<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter\Extractor;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use Symfony\Component\HttpFoundation\File\File;

interface ConverterInterface
{
    /**
     * Convert File to Document
     *
     * @param File $file
     *
     * @return Node
     */
    public function convert(File $file);
}
