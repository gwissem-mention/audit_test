<?php

namespace HopitalNumerique\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ContextualNavigationBundle\Service\LostInformationRetriever;

/**
 * Class CustomExceptionController
 */
class CustomExceptionController extends Controller
{
    /**
     * @return Response
     */
    public function notFoundAction()
    {
        return $this->render(
            '@Twig/Exception/error404.html.twig',
            $this->get(LostInformationRetriever::class)->getLostInformation()
        );
    }

    /**
     * @return Response
     */
    public function accessDeniedAction()
    {
        return $this->render(
            '@Twig/Exception/error403.html.twig',
            $this->get(LostInformationRetriever::class)->getLostInformation()
        );
    }
}
