<?php

namespace HopitalNumerique\ForumBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Post.
 */
class PostManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ForumBundle\Entity\Post';
}