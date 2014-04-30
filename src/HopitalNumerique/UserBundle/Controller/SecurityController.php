<?php

namespace HopitalNumerique\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * {@inheritDoc}
 */
class SecurityController extends BaseController
{
    /**
     * {@inheritDoc}
     */
    protected function renderLogin(array $data)
    {
        //Si il n'y a pas d'utilisateur connecté on affiche le login
        if(!$this->container->get('security.context')->isGranted('ROLE_USER'))
        {        
            $requestAttributes = $this->container->get('request')->attributes;
    
            if ('account_login' === $requestAttributes->get('_route'))
                $template = sprintf('HopitalNumeriqueAccountBundle:Security:login.html.twig');
            else
                $template = sprintf('HopitalNumeriqueUserBundle:Security:login.html.twig');

            //On récupère l'utilisateur qui est connecté
            $user = $this->get('security.context')->getToken()->getUser();
            $user->addNbVisites();
    
            return $this->container->get('templating')->renderResponse($template, $data);
        }
        
        //Sinon on redirige vers la homepage
        return new RedirectResponse($this->container->get('router')->generate('hopital_numerique_homepage'));
    }
}