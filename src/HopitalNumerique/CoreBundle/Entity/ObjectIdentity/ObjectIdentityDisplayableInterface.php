<?php

namespace HopitalNumerique\CoreBundle\Entity\ObjectIdentity;

interface ObjectIdentityDisplayableInterface
{
    /**
     * @return mixed
     */
    public function getObjectIdentityTitle();

    /**
     * @return string
     */
    public function getObjectIdentityType();
}
