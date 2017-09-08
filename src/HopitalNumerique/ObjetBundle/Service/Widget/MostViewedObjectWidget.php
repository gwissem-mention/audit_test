<?php

namespace HopitalNumerique\ObjetBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

/**
 * Class MostViewedObjectWidget
 */
class MostViewedObjectWidget extends WidgetAbstract
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
        $objects = $this->objectRepository->getMostViewedObjectsForUser($this->tokenStorage->getToken()->getUser());

        $html = $this->twig->render('HopitalNumeriqueObjetBundle:widget:most_viewed_objects.html.twig', [
            'objects' => $objects,
        ]);

        $title = $this->translator->trans('most_viewed_object.title', [], 'widget');

        return new Widget('most-viewed-object', $title, $html);
    }
}
