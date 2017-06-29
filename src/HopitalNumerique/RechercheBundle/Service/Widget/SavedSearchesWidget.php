<?php

namespace HopitalNumerique\RechercheBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;

/**
 * Class SavedSearches
 */
class SavedSearchesWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $html = $this->twig->render('HopitalNumeriqueRechercheBundle:widget:saved_searches.html.twig');

        $title = $this->translator->trans('saved_searches.title', [], 'widget');

        return new Widget('saved-searches', $title, $html);
    }
}
