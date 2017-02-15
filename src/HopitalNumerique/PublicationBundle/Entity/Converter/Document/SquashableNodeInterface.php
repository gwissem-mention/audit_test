<?php

namespace HopitalNumerique\PublicationBundle\Entity\Converter\Document;

interface SquashableNodeInterface
{
    /**
     * @return SquashableNodeInterface
     */
    public function getSquashIn();
}
