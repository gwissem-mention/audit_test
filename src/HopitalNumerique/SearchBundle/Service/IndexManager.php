<?php

namespace HopitalNumerique\SearchBundle\Service;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use FOS\ElasticaBundle\Elastica\Index;

/**
 * IndexManager handles index retrieval
 */
class IndexManager extends \FOS\ElasticaBundle\Index\IndexManager
{
    /**
     * Get an index by domain slug
     *
     * @param $slug
     * @return Index
     */
    public function getIndexByDomaine($slug)
    {
        return $this->getIndex($this->getIndexNameByDomaineSlug($slug));
    }

    /**
     * Get Index name by domain
     *
     * @param Domaine $domaine
     * @return string
     */
    public function getIndexNameByDomaine(Domaine $domaine)
    {
        return $this->getIndexNameByDomaineSlug($domaine->getSlug());
    }

    /**
     * Get Index name by domain slug
     *
     * @param $slug
     * @return string
     */
    public function getIndexNameByDomaineSlug($slug)
    {
        return sprintf('cdr_domaine_%s', str_replace('-', '_', $slug));
    }
}
