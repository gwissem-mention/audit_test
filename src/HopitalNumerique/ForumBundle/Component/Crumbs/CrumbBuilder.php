<?php

namespace HopitalNumerique\ForumBundle\Component\Crumbs;

use CCDNForum\ForumBundle\Entity\Forum;
use CCDNForum\ForumBundle\Component\Crumbs\CrumbBuilder as CCDNCrumbs;

class CrumbBuilder extends CCDNCrumbs
{
    /**
     * @param \CCDNForum\ForumBundle\Entity\Forum $forum
     *
     * @return \CCDNForum\ForumBundle\Component\Crumbs\Factory\CrumbTrail
     */
    public function addUserCategoryIndex(Forum $forum = null)
    {
        return $this->createCrumbTrail()
            ->add(
                $forum ?
                    $forum->getName() == 'default' ? 'Index' : $forum->getName()
                    :
                    'Index',
                [
                    'route' => 'ccdn_forum_user_category_index',
                    'params' => [
                        'forumName' => $forum ? $forum->getName() : 'default',
                    ],
                ]
            )
        ;
    }
}
