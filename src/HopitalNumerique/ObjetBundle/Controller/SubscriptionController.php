<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ObjetBundle\Domain\Command\ObjectSubscriptionUpdateCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\ObjectSubscriptionUpdateHandler;
use HopitalNumerique\ObjetBundle\Domain\Command\SubscribeToObjectCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\SubscribeToObjectHandler;
use HopitalNumerique\ObjetBundle\Domain\Command\UnsubscribeToObjectCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\UnsubscribeToObjectHandler;

/**
 * Class SubscriptionController
 */
class SubscriptionController extends Controller
{
    /**
     * @param Request $request
     * @param Objet $object
     * @param Contenu|null $content
     *
     * @return JsonResponse
     */
    public function subscribeAction(Request $request, Objet $object, Contenu $content = null)
    {
        if (true === $request->request->getBoolean('wanted')) {
            $command = new SubscribeToObjectCommand($this->getUser(), $object, $content);
            $this->get(SubscribeToObjectHandler::class)->handle($command);
        } else {
            $command = new UnsubscribeToObjectCommand($this->getUser(), $object, $content);
            $this->get(UnsubscribeToObjectHandler::class)->handle($command);
        }

        return new JsonResponse();
    }
}
