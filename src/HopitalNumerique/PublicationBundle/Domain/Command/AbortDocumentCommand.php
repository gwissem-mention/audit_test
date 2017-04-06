<?php

namespace HopitalNumerique\PublicationBundle\Domain\Command;

class AbortDocumentCommand
{
    /**
     * @var integer $publicationId
     */
    public $publicationId;

    /**
     * DeleteDocumentCommand constructor.
     *
     * @param $publicationId
     */
    public function __construct($publicationId)
    {
        $this->publicationId = $publicationId;
    }

}
