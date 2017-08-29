<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Group;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Event\GroupEvent;

/**
 * Class GroupDocumentCreatedEvent.
 */
class DocumentCreatedEvent extends GroupEvent
{
    /**
     * @var Document $document
     */
    protected $document;

    /**
     * GroupDocumentCreatedEvent constructor.
     *
     * @param Groupe   $group
     * @param Document $document
     */
    public function __construct(Groupe $group, Document $document)
    {
        parent::__construct($group);
        $this->document = $document;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
