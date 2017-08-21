<?php

namespace HopitalNumerique\CoreBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class NotFoundHttpExceptionListener
 */
class NotFoundHttpExceptionListener
{
    /**
     * @var ControllerResolver
     */
    protected $controller_resolver;

    /**
     * @var RequestStack
     */
    protected $request_stack;

    /**
     * @var HttpKernel
     */
    protected $http_kernel;

    /**
     * NotFoundHttpExceptionListener constructor.
     *
     * @param ControllerResolverInterface $controller_resolver
     * @param RequestStack                $request_stack
     * @param HttpKernel                  $http_kernel
     */
    public function __construct(
        ControllerResolverInterface $controller_resolver,
        RequestStack $request_stack,
        HttpKernel $http_kernel
    ) {
        $this->controller_resolver = $controller_resolver;
        $this->request_stack = $request_stack;
        $this->http_kernel = $http_kernel;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NotFoundHttpException) {
            $request = new Request();
            $request->attributes->set('_controller', 'HopitalNumeriqueCoreBundle:CustomException:notFound');
            $controller = $this->controller_resolver->getController($request);

            $path['_controller'] = $controller;
            $subRequest          = $this->request_stack->getCurrentRequest()->duplicate([], null, $path);

            $event->setResponse(
                $this->http_kernel->handle($subRequest, HttpKernelInterface::MASTER_REQUEST)
            );
        }
    }
}