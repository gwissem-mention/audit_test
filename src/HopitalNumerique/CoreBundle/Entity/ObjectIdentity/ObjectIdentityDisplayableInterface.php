<?php

namespace HopitalNumerique\CoreBundle\Entity\ObjectIdentity;

interface ObjectIdentityDisplayableInterface
{
    /**
     * @return mixed
     */
    public function getObjectIdentityTitle();

    /**
     * @return array
     */
    public function getObjectIdentityCategories();

    /**
     * @return string
     */
    public function getObjectIdentityDescription();

    /**
     * @return string
     */
    public function getObjectIdentityType();
}
