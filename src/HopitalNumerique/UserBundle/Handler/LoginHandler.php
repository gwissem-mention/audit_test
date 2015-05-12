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
    protected $defaultAuthenticationSuccessHandler;
    
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $urlParameter = $request->getSession()->get('urlToRedirect');

        if(!is_null($urlParameter) && $urlParameter !== "")     
        {   
            return new RedirectResponse($urlParameter);     
        }

        $urlFirewall = $request->getSession()->get('_security.frontoffice_connecte.target_path');

        return new RedirectResponse(is_null($urlFirewall) || $urlFirewall == "" ? $this->router->generate('hopital_numerique_homepage' ) : $urlFirewall);
    }
    
}