<?php

namespace HopitalNumerique\AutodiagBundle\Service\Autodiag;

use Doctrine\Common\Persistence\ObjectManager;
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

    public function getHistory($type)
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

        return $this->manager->getRepository(History::class)->getHistoryByType($type);
    }
}
