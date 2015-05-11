<?php

namespace HopitalNumerique\UserBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LoginHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $previousUrlContenu = $request->getSession()->get('previous_url_contenu');
        $request->getSession()->set('previous_url_contenu', "");

        if(!is_null($previousUrlContenu) && $previousUrlContenu !== "")     
        {   
            return new RedirectResponse($previousUrlContenu);     
        }
        else
        {
            $urlConnected = $request->getSession()->get('_security.frontoffice_connecte.target_path');
            $request->getSession()->set('_security.frontoffice_connecte.target_path', "");

            return (!is_null($urlConnected) && $urlConnected !== "" )  ? new RedirectResponse( $urlConnected ) : new RedirectResponse($this->router->generate('hopital_numerique_homepage'));
        }
    }
    
}