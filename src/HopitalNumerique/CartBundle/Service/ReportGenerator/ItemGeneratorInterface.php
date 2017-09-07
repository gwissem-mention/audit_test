<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;

interface ItemGeneratorInterface
{
    /**
     * @param $object
     *
     * @return boolean
     */
    public function support($object);

    /**
     * @param $object
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($object, Report $report);
}
