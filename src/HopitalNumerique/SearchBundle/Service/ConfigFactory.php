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

    /**
     * ConfigFactory constructor.
     * @param IndexManager $indexManager
     * @param CurrentDomaine $domainStorage
     */
    public function __construct(IndexManager $indexManager, CurrentDomaine $domainStorage)
    {
        $this->indexManager = $indexManager;
        $this->domainStorage = $domainStorage;
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
        ];
    }
}
