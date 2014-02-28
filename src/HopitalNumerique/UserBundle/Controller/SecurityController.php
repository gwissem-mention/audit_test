<?php

namespace HopitalNumerique\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;

/**
 * {@inheritDoc}
 */
class SecurityController extends BaseController
{
    /**
     * {@inheritDoc}
     */
    public function renderLogin(array $data)
    {
        $requestAttributes = $this->container->get('request')->attributes;

        if ('account_login' === $requestAttributes->get('_route'))
            $template = sprintf('HopitalNumeriqueAccountBundle:Security:login.html.twig');
        else
            $template = sprintf('HopitalNumeriqueUserBundle:Security:login.html.twig');

        return $this->container->get('templating')->renderResponse($template, $data);
    }
}