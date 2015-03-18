<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserPostController as UserPostControllerBase;
use HopitalNumerique\ForumBundle\Entity\Post;

class UserPostController extends UserPostControllerBase
{
    public function editProcessAction($forumName, $postId)
    {
        $post = $this->getPostModel()->findOnePostByIdWithTopicAndBoard($postId, true);
        $this->container->get('hopitalnumerique_forum.service.piece_jointe')->verifyPieceJointe($post);
        
        return parent::editProcessAction($forumName, $postId);
    }

    /**
     * Télécharge la PJ du post.
     * 
     * @param \HopitalNumerique\ForumBundle\Entity\Post $post
     */
    public function downloadPieceJointeAction(Post $post)
    {
        $nomPieceJointe = substr($post->getPieceJointe(), 0, strrpos($post->getPieceJointe(), '_')).substr($post->getPieceJointe(), strrpos($post->getPieceJointe(), '.'));
        
        $options = array(
            'serve_filename' => $nomPieceJointe,
            'absolute_path'  => true,
            'inline'         => false
        );
    
        if (file_exists('../'.$post->getPieceJointeUrl()))
            return $this->container->get('igorw_file_serve.response_factory')->create('../'.$post->getPieceJointeUrl(), 'application/force-download', $options);

        
        $this->container->get('session')->getFlashBag()->add('danger', 'Fichier introuvable.');
        return $this->redirectResponse($this->path('ccdn_forum_user_topic_show', array('topicId' => $post->getTopic()->getId())));
    }
}
