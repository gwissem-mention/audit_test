<?php
namespace HopitalNumerique\ForumBundle\DependencyInjection;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Manager\PostManager;

/**
 * Service gérant la PJ d'un Post.
 * 
 * @author Rémi Leclerc
 */
class PieceJointe
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request Request
     */
    private $request;
    
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;
    
    /**
     * @var \HopitalNumerique\ForumBundle\Manager\PostManager PostManager
     */
    private $postManager;
    
    /**
     * @var string Extensions autorisées
     */
    private $extensionsAutorisees;
    
    public function __construct(Request $request, SessionInterface $session, PostManager $postManager, $extensionsAutorisees)
    {
        $this->request = $request;
        $this->session = $session;
        $this->postManager = $postManager;
        $this->extensionsAutorisees = $extensionsAutorisees;
    }
    
    /**
     * Vérifie la pièce jointe du Post.
     *
     * @return boolean VRAI si aucune pièce jointe ou PJ valide.
     */
    public function verifyPieceJointe(Post $post = null)
    {
        $this->gereSuppression($post);
        
        if ($this->request->files->has('Post'))
        {
            $fichiersPost = $this->request->files->get('Post');
    
            if (isset($fichiersPost['pieceJointeFile']) && $fichiersPost['pieceJointeFile'] instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && $fichiersPost['pieceJointeFile']->getSize() > 0)
            {
                $pieceJointeExtension = substr($fichiersPost['pieceJointeFile']->getClientOriginalName(), strrpos($fichiersPost['pieceJointeFile']->getClientOriginalName(), '.') + 1);

                if (!in_array($pieceJointeExtension, explode(',', $this->extensionsAutorisees)))
                {
                    $this->session->getFlashBag()->add('warning', 'La pièce jointe n\'a pas été enregistrée car les extensions autorisées sont '.$this->extensionsAutorisees.'.');
                    unset($fichiersPost['pieceJointeFile']);
                    $this->request->files->set('Post', $fichiersPost);

                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Supprime la PJ si demandé.
     */
    private function gereSuppression(Post $post = null)
    {
        if (null !== $post && $this->request->request->has('Post'))
        {
            $donneesPost = $this->request->request->get('Post');
            
            if (isset($donneesPost['pieceJointeSuppression']) && true == $donneesPost['pieceJointeSuppression'])
            {
                $post->deletePieceJointe();
                $this->postManager->save($post);
            }
        }
    }
}
