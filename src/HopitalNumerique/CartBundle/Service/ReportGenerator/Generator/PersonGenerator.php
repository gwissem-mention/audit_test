<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Model\Report\Person;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class PersonGenerator implements ItemGeneratorInterface
{
    /**
     * @param $object
     *
     * @return bool
     */
    public function support($object)
    {
        return $object instanceof User;
    }

    /**
     * @param User $person
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($person, Report $report)
    {
        $item = new Person($person, []);

        return $item;
    }

}
