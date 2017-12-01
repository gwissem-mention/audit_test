<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

interface WallItemInterface
{
    /**
     * @return \DateTime
     */
    public function getDate();

    /**
     * @return string
     */
    public function getType();
}
