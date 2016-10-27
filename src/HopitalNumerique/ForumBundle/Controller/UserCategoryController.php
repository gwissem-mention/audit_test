<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserCategoryController as UserCategoryControllerBase;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UserCategoryController extends UserCategoryControllerBase
{
    /**
     * Recalcule les derniers messages.
     *
     * @param $token
     *
     * @return Response
     */
    public function recalculateLastMessagesAction($token)
    {
        if ('jkfghdsfhfbgdhfsbgdfvbdkjfg' != $token) {
            return new Response('Traitement non autorisé.');
        }

        $this->container->get('hopitalnumerique_forum.manager.board')->recalculateAllLastMessages();

        return new Response('Derniers messages recalculés.');
    }

    /**
     * @param string $forumName
     *
     * @return string|RedirectResponse
     */
    public function indexAction($forumName)
    {
        if ($forumName != 'default') {
            $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));

            if (!$this->getAuthorizer()->canShowForum($forum)) {
                if ($this->getUser() === 'anon.') {
                    $redirectUrl = $this->container->get('router')
                        ->generate('ccdn_forum_user_category_index', ['forumName' => $forumName])
                    ;
                    $redirectUrl = rtrim(strtr(base64_encode($redirectUrl), '+/', '-_'), '=');
                    $url = $this->container->get('router')
                        ->generate('account_login', ['urlToRedirect' => $redirectUrl])
                    ;

                    return new RedirectResponse($url, 302);
                } else {
                    throw new AccessDeniedException();
                }
            }
        } else {
            $forum = null;

            if (!$this->getAuthorizer()->canShowForumUnassigned()) {
                if ($this->getUser() === 'anon.') {
                    $redirectUrl = $this->container->get('router')
                        ->generate('ccdn_forum_user_category_index', ['forumName' => $forumName])
                    ;
                    $redirectUrl = rtrim(strtr(base64_encode($redirectUrl), '+/', '-_'), '=');
                    $url = $this->container->get('router')
                        ->generate('account_login', ['urlToRedirect' => $redirectUrl])
                    ;

                    return new RedirectResponse($url, 302);
                } else {
                    throw new AccessDeniedException();
                }
            }
        }

        $categories = $this->getCategoryModel()->findAllCategoriesWithBoardsForForumByName($forumName);

        return $this->renderResponse('CCDNForumForumBundle:User:Category/index.html.', [
            'crumbs'          => $this->getCrumbs()->addUserCategoryIndex($forum),
            'forum'           => $forum,
            'forumName'       => $forumName,
            'categories'      => $categories,
            'topics_per_page' => $this->container->getParameter('ccdn_forum_forum.board.user.show.topics_per_page'),
        ]);
    }

    public function showAction($forumName, $categoryId)
    {
        $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));
        $this->isFound($category = $this->getCategoryModel()->findOneCategoryByIdWithBoards($categoryId));

        if (!$this->getAuthorizer()->canShowCategory($category, $forum)) {
            if ($this->getUser() === 'anon.') {
                $redirectUrl = $this->container->get('router')
                    ->generate('ccdn_forum_user_category_show', ['categoryId' => $categoryId])
                ;
                $redirectUrl = rtrim(strtr(base64_encode($redirectUrl), '+/', '-_'), '=');
                $url = $this->container->get('router')
                    ->generate('account_login', ['urlToRedirect' => $redirectUrl])
                ;

                return new RedirectResponse($url, 302);
            } else {
                throw new AccessDeniedException();
            }
        }

        return $this->renderResponse('CCDNForumForumBundle:User:Category/show.html.', [
            'crumbs'          => $this->getCrumbs()->addUserCategoryShow($forum, $category),
            'forum'           => $forum,
            'forumName'       => $forumName,
            'category'        => $category,
            'categories'      => [$category],
            'topics_per_page' => $this->container->getParameter('ccdn_forum_forum.board.user.show.topics_per_page'),
        ]);
    }
}
