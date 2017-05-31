<?php

namespace HopitalNumerique\SearchBundle\Service;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;

class ConfigFactory
{
    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var CurrentDomaine
     */
    protected $domainStorage;

    protected $aggregationParameter;

    /**
     * ConfigFactory constructor.
     * @param IndexManager $indexManager
     * @param CurrentDomaine $domainStorage
     */
    public function __construct(IndexManager $indexManager, CurrentDomaine $domainStorage, $aggregationParameter)
    {
        $this->indexManager = $indexManager;
        $this->domainStorage = $domainStorage;
        $this->aggregationParameter = $aggregationParameter;
    }

    /**
     * Create search configuration array
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'index' => $this->indexManager->getIndexNameByDomaine($this->domainStorage->get()),
            'aggregation' => $this->aggregationParameter,
        ];
    }
}
