<?php
namespace HopitalNumerique\ForumBundle\Controller;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Reece Fowell <reece@codeconsortium.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @version  Release: 2.0
 * @link     https://github.com/codeconsortium/CCDNForumForumBundle
 *
 */
class AdminForumController extends \CCDNForum\ForumBundle\Controller\AdminForumBaseController
{
    /**
     *
     * @access public
     * @return RenderResponse
     */
    public function listAction()
    {
        $this->isAuthorised('ROLE_SUPER_ADMIN');
        $forums = $this->getForumModel()->findAllForums();
        $response = $this->renderResponse('CCDNForumForumBundle:Admin:/Forum/list.html.', array(
            'crumbs' => $this->getCrumbs()->addAdminManageForumsIndex(),
            'forums' => $forums
        ));

        return $response;
    }
}
