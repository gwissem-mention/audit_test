<?php

namespace HopitalNumerique\UserBundle\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    private $_objetManager;
    private $_securityContext;

    public function __construct( $objetManager, $securityContext )
    {
        $this->_objetManager    = $objetManager;
        $this->_securityContext = $securityContext;
    }

    /**
     * Si le logout est autorisé, on délock les objets consultés par l'utilisateur (s'il en existe)
     *
     * @param  Request $request La requete
     *
     * @return RedirectResponse
     */
    public function onLogoutSuccess(Request $request) 
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->_securityContext->getToken()->getUser();

        //do remove locked stuff
        $objets = $this->_objetManager->findBy( array('lockedBy'=>$user) );
        foreach($objets as $objet){
            $objet->setLock( 0 );
            $objet->setLockedBy( null );

            $this->_objetManager->save($objet);
        }

        return new RedirectResponse( $request->headers->get('referer') );
    }
}