<?php

namespace HopitalNumerique\ObjetBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

/**
 * Class SuggestionWidget
 */
class SuggestionWidget extends WidgetAbstract
{
    /**
     * @var ObjetRepository $objectRepository
     */
    protected $objectRepository;

    /**
     * @param ObjetRepository $objectRepository
     */
    public function setObjectRepository(ObjetRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $html = $this->twig->render('HopitalNumeriqueObjetBundle:widget:suggestion.html.twig');

        $title = $this->translator->trans('suggestion.title', [], 'widget');

        return new Widget('object-suggestion', $title, $html);
    }
}
