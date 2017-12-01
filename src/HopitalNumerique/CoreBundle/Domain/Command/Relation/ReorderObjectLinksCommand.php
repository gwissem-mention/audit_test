<?php

namespace HopitalNumerique\CoreBundle\Domain\Command\Relation;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;

class ReorderObjectLinksCommand
{
    /**
     * @var ObjectIdentity
     */
    public $sourceObjectIdentity;

    /**
     * @var array
     */
    public $orderedObjectsIdentityId;

    /**
     * ReorderObjectLinksCommand constructor.
     *
     * @param ObjectIdentity $sourceObjectIdentity
     * @param array $orderedObjectsIdentityId
     */
    public function __construct(ObjectIdentity $sourceObjectIdentity, array $orderedObjectsIdentityId)
    {
        $this->sourceObjectIdentity = $sourceObjectIdentity;
        $this->orderedObjectsIdentityId = $orderedObjectsIdentityId;
    }
}
