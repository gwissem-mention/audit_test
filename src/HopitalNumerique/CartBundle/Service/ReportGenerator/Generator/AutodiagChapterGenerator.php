<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\AutodiagChapter;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class AutodiagChapterGenerator implements ItemGeneratorInterface
{
    /**
     * @param $object
     *
     * @return bool
     */
    public function support($object)
    {
        return $object instanceof Autodiag\Container;
    }

    /**
     * @param Autodiag\Container $chapter
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($chapter, Report $report)
    {
        $item = new AutodiagChapter($chapter);

        return $item;
    }

}
