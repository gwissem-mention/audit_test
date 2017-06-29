<?php

namespace HopitalNumerique\RechercheBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

/**
 * Class NewSearchResultsWidget
 */
class NewSearchResultsWidget extends WidgetAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $html = $this->twig->render('HopitalNumeriqueRechercheBundle:widget:new_search_results.html.twig');

        $title = $this->translator->trans('new_search_results.title', [], 'widget');

        return new Widget('new-search-results', $title, $html);
    }
}
