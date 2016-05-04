<?php

namespace HopitalNumerique\UserBundle\Handler;

use HopitalNumerique\RechercheBundle\DependencyInjection\Referencement\RequeteSession;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    private $_objetManager;
    private $_securityContext;

    public function __construct($objetManager, $securityContext, RequeteSession $requeteSession)
    {
        $this->_objetManager    = $objetManager;
        $this->_securityContext = $securityContext;
        $this->requeteSession = $requeteSession;
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
        $user = null;

        //On récupère l'utilisateur qui est connecté
        if(!is_null($this->_securityContext->getToken()))
        {
            $user = $this->_securityContext->getToken()->getUser();
        }

        //do remove locked stuff
        if(!is_null($user))
        {
            $objets = $this->_objetManager->findBy( array('lockedBy'=>$user) );
            foreach($objets as $objet){
                $objet->setLock( 0 );
                $objet->setLockedBy( null );

                $this->_objetManager->save($objet);
            }
        }

        $this->requeteSession->remove();

        return new RedirectResponse( $request->headers->get('referer') );
    }
}