<?php

namespace HopitalNumerique\ForumBundle\Controller;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminForumResponseEvent;

/**
 * @category CCDNForum
 *
 * @author   Reece Fowell <reece@codeconsortium.com>
 * @license  http://opensource.org/licenses/MIT MIT
 *
 * @version  Release: 2.0
 *
 * @see     https://github.com/codeconsortium/CCDNForumForumBundle
 */
class AdminForumController extends \CCDNForum\ForumBundle\Controller\AdminForumBaseController
{
    use ForumControllerAuthorizationCheckerTrait;

    /**
     * @return RenderResponse
     */
    public function listAction()
    {
        // TODO : Utiliser la gestion des droits du backoffice
        if (!$this->getUser() instanceof User || !$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $forums = $this->getForumModel()->findAllForums();
        $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/list.html.', [
            'crumbs' => $this->getCrumbs()->addAdminManageForumsIndex(),
            'forums' => $forums,
        ]);

        return $response;
    }

    /**
     * @return RenderResponse
     */
    public function createAction()
    {
        // TODO : Utiliser la gestion des droits du backoffice
        if (!$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $formHandler = $this->getFormHandlerToCreateForum();
        $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/create.html.', [
            'crumbs' => $this->getCrumbs()->addAdminManageForumsCreate(),
            'form' => $formHandler->getForm()->createView(),
        ]);

        $this->dispatch(ForumEvents::ADMIN_FORUM_CREATE_RESPONSE, new AdminForumResponseEvent($this->getRequest(), $response));

        return $response;
    }

    /**
     * @return RenderResponse
     */
    public function createProcessAction()
    {
        // TODO : Utiliser la gestion des droits du backoffice
        if (!$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $formHandler = $this->getFormHandlerToCreateForum();

        if ($formHandler->process()) {
            $response = $this->redirectResponse($this->path('ccdn_forum_admin_forum_list'));
        } else {
            $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/create.html.', [
                'crumbs' => $this->getCrumbs()->addAdminManageForumsCreate(),
                'form' => $formHandler->getForm()->createView(),
            ]);
        }

        $this->dispatch(ForumEvents::ADMIN_FORUM_CREATE_RESPONSE, new AdminForumResponseEvent($this->getRequest(), $response, $formHandler->getForm()->getData()));

        return $response;
    }

    /**
     * @return RenderResponse
     */
    public function editAction($forumId)
    {
        // TODO : Utiliser la gestion des droits du backoffice
        if (!$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $this->isFound($forum = $this->getForumModel()->findOneForumById($forumId));
        $formHandler = $this->getFormHandlerToUpdateForum($forum);
        $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/edit.html.', [
            'crumbs' => $this->getCrumbs()->addAdminManageForumsEdit($forum),
            'form' => $formHandler->getForm()->createView(),
            'forum' => $forum,
        ]);

        $this->dispatch(ForumEvents::ADMIN_FORUM_EDIT_RESPONSE, new AdminForumResponseEvent($this->getRequest(), $response, $formHandler->getForm()->getData()));

        return $response;
    }

    /**
     * @return RenderResponse
     */
    public function editProcessAction($forumId)
    {
        // TODO : Utiliser la gestion des droits du backoffice
        if (!$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $this->isFound($forum = $this->getForumModel()->findOneForumById($forumId));
        $formHandler = $this->getFormHandlerToUpdateForum($forum);

        if ($formHandler->process()) {
            $response = $this->redirectResponse($this->path('ccdn_forum_admin_forum_list'));
        } else {
            $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/edit.html.', [
                'crumbs' => $this->getCrumbs()->addAdminManageForumsEdit($forum),
                'form' => $formHandler->getForm()->createView(),
                'forum' => $forum,
            ]);
        }

        $this->dispatch(ForumEvents::ADMIN_FORUM_EDIT_RESPONSE, new AdminForumResponseEvent($this->getRequest(), $response, $formHandler->getForm()->getData()));

        return $response;
    }

    /**
     * @return RenderResponse
     */
    public function deleteAction($forumId)
    {
        // TODO : Utiliser la gestion des droits du backoffice
        if (!$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107')
            && !$this->isGranted('ROLE_SUPER_ADMIN')
            && !$this->isGranted('ROLE_ADMINISTRATEUR_DE_DOMAINE_106')
        ) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $this->isFound($forum = $this->getForumModel()->findOneForumById($forumId));
        $formHandler = $this->getFormHandlerToDeleteForum($forum);
        $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/delete.html.', [
            'crumbs' => $this->getCrumbs()->addAdminManageForumsDelete($forum),
            'form' => $formHandler->getForm()->createView(),
            'forum' => $forum,
        ]);

        $this->dispatch(ForumEvents::ADMIN_FORUM_DELETE_RESPONSE, new AdminForumResponseEvent($this->getRequest(), $response, $formHandler->getForm()->getData()));

        return $response;
    }

    /**
     * @return RedirectResponse
     */
    public function deleteProcessAction($forumId)
    {
        if (!$this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107')
            && !$this->isGranted('ROLE_SUPER_ADMIN')
            && !$this->isGranted('ROLE_ADMINISTRATEUR_DE_DOMAINE_106')
        ) {
            throw new AccessDeniedException('You do not have permission to use this resource.');
        }
        //$this->isAuthorised('ROLE_SUPER_ADMIN');

        $this->isFound($forum = $this->getForumModel()->findOneForumById($forumId));
        $formHandler = $this->getFormHandlerToDeleteForum($forum);

        if ($formHandler->process()) {
            $response = $this->redirectResponse($this->path('ccdn_forum_admin_forum_list'));
        } else {
            $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/delete.html.', [
                'crumbs' => $this->getCrumbs()->addAdminManageForumsDelete($forum),
                'form' => $formHandler->getForm()->createView(),
                'forum' => $forum,
            ]);
        }

        $this->dispatch(ForumEvents::ADMIN_FORUM_DELETE_RESPONSE, new AdminForumResponseEvent($this->getRequest(), $response, $formHandler->getForm()->getData()));

        return $response;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
