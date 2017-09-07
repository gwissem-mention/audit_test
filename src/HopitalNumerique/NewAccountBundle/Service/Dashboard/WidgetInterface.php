<?php

namespace HopitalNumerique\NewAccountBundle\Service\Dashboard;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;

interface WidgetInterface
{
    /**
     * Return a hydrated Widget
     *
     * @return Widget
     */
    public function getWidget();
}
