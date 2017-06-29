<?php

namespace HopitalNumerique\ObjetBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

/**
 * Class LastViewedObjectWidget
 */
class LastViewedObjectWidget extends WidgetAbstract
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
        $objects = $this->objectRepository->getLastViewedObjects($this->tokenStorage->getToken()->getUser());

        $html = $this->twig->render('HopitalNumeriqueObjetBundle:widget:last_viewed_objects.html.twig', [
            'objects' => $objects,
        ]);

        $title = $this->translator->trans('last_viewed_objects.title', [], 'widget');

        return new Widget('last-viewed-object', $title, $html);
    }
}
