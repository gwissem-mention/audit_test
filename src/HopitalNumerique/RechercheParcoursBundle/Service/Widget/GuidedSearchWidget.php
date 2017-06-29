<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;

/**
 * Class GuidedSearchWidget
 */
class GuidedSearchWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $html = $this->twig->render('HopitalNumeriqueRechercheParcoursBundle:widget:guided_search.html.twig');

        $title = $this->translator->trans('guided_search.title', [], 'widget');

        return new Widget('guided-search', $title, $html);
    }
}
