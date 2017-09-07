<?php

namespace HopitalNumerique\AutodiagBundle\Service\Autodiag;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\History;

class HistoryReader
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    public function __construct(ObjectManager $om)
    {
        $this->manager = $om;
    }

    public function getHistory(Autodiag $autodiag, $type)
    {
        if (!in_array($type, History::getTypeList())) {
            throw new \Exception(
                sprintf(
                    'Type %s not found. You must use one of these : %s',
                    $type,
                    implode(', ', History::getTypeList())
                )
            );
        }

        return $this->manager->getRepository(History::class)->getHistoryByType($autodiag, $type);
    }

    /**
     * Retrieves autodiag history (all types)
     *
     * @param Autodiag $autodiag
     *
     * @return array
     */
    public function getHistoryByAutodiag(Autodiag $autodiag)
    {
        return $this->manager->getRepository(History::class)->getHistoryByAutodiag($autodiag);
    }
}
