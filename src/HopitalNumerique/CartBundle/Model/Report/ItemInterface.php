<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;

interface ItemInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return EntityHasReference[]
     */
    public function getReferences();
}
