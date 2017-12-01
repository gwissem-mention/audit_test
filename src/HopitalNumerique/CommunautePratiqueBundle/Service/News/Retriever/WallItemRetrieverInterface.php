<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;

interface WallItemRetrieverInterface
{
    /**
     * Get news items to display in news wall
     *
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null);

    /**
     * If information can be displayed for no-logged users or not
     *
     * @return boolean
     */
    public function isPublic();
}
