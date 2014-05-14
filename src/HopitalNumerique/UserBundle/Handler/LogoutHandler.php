<?php

namespace HopitalNumerique\UserBundle\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    private $_objetManager;

    public function __construct( $objetManager )
    {
        $this->_objetManager = $objetManager;
    }

    public function onLogoutSuccess(Request $request) 
    {
        $referer = $request->headers->get('referer');
        
        // //do remove locked stuff
        // $objets = $this->_objetManager->find

        // $objet->setLock( 0 );
        // $objet->setLockedBy( null );

        return new RedirectResponse($referer);
    }
}