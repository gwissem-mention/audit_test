<?php

namespace HopitalNumerique\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * {@inheritDoc}
 */
class SecurityController extends BaseController
{

    public function loginCustomAction(Request $request, $urlToRedirect = "")
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        $session->set('urlToRedirect', base64_decode(str_pad(strtr($urlToRedirect, '-_', '+/'), strlen($urlToRedirect) % 4, '=', STR_PAD_RIGHT)));

        if (class_exists('\Symfony\Component\Security\Core\Security')) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
        } else {
            // BC for SF < 2.6
            $authErrorKey = SecurityContextInterface::AUTHENTICATION_ERROR;
            $lastUsernameKey = SecurityContextInterface::LAST_USERNAME;
        }

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        if ($this->has('security.csrf.token_manager')) {
            $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        } else {
            // BC for SF < 2.4
            $csrfToken = $this->has('form.csrf_provider')
                ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null;
        }

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function renderLogin(array $data)
    {
        //Si il n'y a pas d'utilisateur connecté on affiche le login
        if(!$this->container->get('security.context')->isGranted('ROLE_USER'))
        {        
            $requestAttributes = $this->container->get('request')->attributes;
    
            if ('account_login' === $requestAttributes->get('_route')
                || 'account_login_custom' === $requestAttributes->get('_route') )
                $template = sprintf('HopitalNumeriqueAccountBundle:Security:login.html.twig');
            else
                $template = sprintf('HopitalNumeriqueUserBundle:Security:login.html.twig');
    
            return $this->container->get('templating')->renderResponse($template, $data);
        }
        
        //Sinon on redirige vers la homepage
        return new RedirectResponse($this->container->get('router')->generate('hopital_numerique_homepage'));
    }
}