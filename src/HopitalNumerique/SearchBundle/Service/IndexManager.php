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
     * @var string
     */
    protected $indexPrefix;

    /**
     * IndexManager constructor.
     * @param array $indexes
     * @param Index $defaultIndex
     * @param $indexPrefix
     */
    public function __construct(array $indexes, Index $defaultIndex, $indexPrefix)
    {
        parent::__construct($indexes, $defaultIndex);

        $this->indexPrefix = $indexPrefix;
    }

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
        return sprintf('%s_%s', $this->indexPrefix, str_replace('-', '_', $slug));
    }
}
