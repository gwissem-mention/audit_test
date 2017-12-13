<?php

namespace HopitalNumerique\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class LoginEntryPointListener implements AuthenticationEntryPointInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * LoginEntryPointListener constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * This method receives the current Request object and the exception by which the exception
     * listener was triggered.
     *
     * The method should return a Response object
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(null, 401);
        }

        return new RedirectResponse($this->router->generate('account_login'));
    }
}
