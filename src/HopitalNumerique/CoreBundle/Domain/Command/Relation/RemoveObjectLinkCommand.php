<?php

namespace HopitalNumerique\CoreBundle\Domain\Command\Relation;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;

class RemoveObjectLinkCommand
{
    /**
     * @var ObjectIdentity $sourceObjectIdentity
     */
    public $sourceObjectIdentity;

    /**
     * @var ObjectIdentity $targetObjectIdentity
     */
    public $targetObjectIdentity;

    /**
     * RemoveObjectLinkCommand constructor.
     *
     * @param ObjectIdentity $sourceObjectIdentity
     * @param ObjectIdentity $targetObjectIdentity
     */
    public function __construct(ObjectIdentity $sourceObjectIdentity, ObjectIdentity $targetObjectIdentity)
    {
        $this->sourceObjectIdentity = $sourceObjectIdentity;
        $this->targetObjectIdentity = $targetObjectIdentity;
    }
}
