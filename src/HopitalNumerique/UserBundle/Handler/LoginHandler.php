<?php

namespace HopitalNumerique\UserBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use Nodevo\AclBundle\Manager\AclManager;

class LoginHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $defaultAuthenticationSuccessHandler;
    protected $_userManager;
    protected $_domaineManager;
    protected $_aclManager;
    
    public function __construct(Router $router, $securityContext, AclManager $aclManager, UserManager $userManager, DomaineManager $domaineManager )
    {
        $this->router           = $router;
        $this->_securityContext = $securityContext;
        $this->_userManager     = $userManager;
        $this->_domaineManager  = $domaineManager;
        $this->_aclManager      = $aclManager;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $urlParameter     = $request->getSession()->get('urlToRedirect');
        $domaineCurrentId = $request->getSession()->get('domaineId');

        //On récupère l'utilisateur qui est connecté
        if(!is_null($this->_securityContext->getToken()))
        {
            $user = $this->_securityContext->getToken()->getUser();
        }

        //Si l'utilisateur n'a pas accès au BackOffice et qu'il n'a pas encore ce domaine on lui assigne
        if(!$this->_aclManager->checkAuthorization($this->router->generate('hopital_numerique_admin_homepage' ), $user) && !in_array($domaineCurrentId, $user->getDomainesId()))
        {
            //Récupération de l'entité
            $domaineCurrent = $this->_domaineManager->findOneById($domaineCurrentId);
            
            $userDomaines   = $user->getDomaines();
            $userDomaines[] = $domaineCurrent;

            $user->setDomaines($userDomaines);
            $this->_userManager->save($user);
        }

        if(!is_null($urlParameter) && $urlParameter !== "")     
        {   
            return new RedirectResponse($urlParameter);     
        }

        $urlFirewall = $request->getSession()->get('_security.frontoffice_connecte.target_path');

        return new RedirectResponse(is_null($urlFirewall) || $urlFirewall == "" ? $this->router->generate('hopital_numerique_homepage' ) : $urlFirewall);
    }
    
}