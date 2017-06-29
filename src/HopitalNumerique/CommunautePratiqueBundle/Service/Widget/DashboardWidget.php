<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

/**
 * Class DashboardWidget
 */
class DashboardWidget extends WidgetAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $html = $this->twig->render('@HopitalNumeriqueCommunautePratique/Widget/widget.html.twig');

        $title = $this->translator->trans('title', [], 'cdpWidget');

        return new Widget('cdp', $title, $html);
    }
}
