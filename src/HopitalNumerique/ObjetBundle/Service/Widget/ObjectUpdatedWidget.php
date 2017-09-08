<?php

namespace HopitalNumerique\ObjetBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;

/**
 * Class ObjectUpdatedWidget
 */
class ObjectUpdatedWidget extends WidgetAbstract
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
        $objects = $this->objectRepository->getUpdatedObjectsSinceLastView($this->tokenStorage->getToken()->getUser());
        
        $html = $this->twig->render('HopitalNumeriqueObjetBundle:widget:object_updated.html.twig', [
            'objects' => $objects,
        ]);

        $title = $this->translator->trans('updated_object.title', [], 'widget');

        return new Widget('object-updated', $title, $html);
    }
}
