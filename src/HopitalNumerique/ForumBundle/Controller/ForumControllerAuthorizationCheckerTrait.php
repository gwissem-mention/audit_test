<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait ForumControllerAuthorizationCheckerTrait
{
    public function isAuthorised($role)
    {
        try {
            parent::isAuthorised($role);
        } catch (AccessDeniedException $e) {
            $this->getContainer()->get('session')->set(
                'urlToRedirect',
                $this->getContainer()->get('router')->generate(
                    $this->getContainer()->get('request')->attributes->get('_route'),
                    $this->getContainer()->get('request')->attributes->get('_route_params')
                )
            );
            throw $e;
        }
    }

    /**
     * @return ContainerInterface
     */
    abstract protected function getContainer();
}
