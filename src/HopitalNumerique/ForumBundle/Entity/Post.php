<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Post as BasePost;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Post extends BasePost
{
    /**
     * @var string
     */
    private $pieceJointe;

    /**
     * @var \Symfony\Component\HttpFoundation\File\File
     */
    private $pieceJointeFile;

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Set pieceJointe
     *
     * @param string $pieceJointe
     * @return Post
     */
    public function setPieceJointe($pieceJointe)
    {
        $this->pieceJointe = $pieceJointe;

        return $this;
    }

    /**
     * Get pieceJointe
     *
     * @return string 
     */
    public function getPieceJointe()
    {
        return $this->pieceJointe;
    }

    /**
     * Get pieceJointeDir
     *
     * @return string 
     */
    public function getPieceJointeDir()
    {
        return 'files/forum/pj';
    }

    /**
     * Get pieceJointeUrl
     *
     * @return string 
     */
    public function getPieceJointeUrl()
    {
        return $this->getPieceJointeDir().'/'.$this->pieceJointe;
    }

    /**
     * Set pieceJointeFile
     *
     * @param string $pieceJointeFile
     * @return Post
     */
    public function setPieceJointeFile($pieceJointeFile)
    {
        $this->pieceJointeFile = $pieceJointeFile;
        
        if (null !== $this->pieceJointeFile)
            $this->uploadPieceJointe();

        return $this;
    }

    /**
     * Get pieceJointeFile
     *
     * @return string 
     */
    public function getPieceJointeFile()
    {
        return $this->pieceJointeFile;
    }

    /**
     * Get pieceJointeUrl
     *
     * @return string 
     */
    public function uploadPieceJointe()
    {
        if (null !== $this->pieceJointe && file_exists(__ROOT_DIRECTORY__.'/'.$this->getPieceJointeUrl()))
            unlink(__ROOT_DIRECTORY__.'/'.$this->getPieceJointeUrl());
        
        $aujourdhui = new \DateTime();
        
        $extension = substr($this->pieceJointeFile->getClientOriginalName(), strrpos($this->pieceJointeFile->getClientOriginalName(), '.') + 1);
        $nomSansExtension = substr($this->pieceJointeFile->getClientOriginalName(), 0, strrpos($this->pieceJointeFile->getClientOriginalName(), '.'));
        $this->pieceJointe = substr($nomSansExtension, 0, 100).'_'.$aujourdhui->getTimestamp().'.'.$extension;
        $this->pieceJointeFile->move(__ROOT_DIRECTORY__.'/'.$this->getPieceJointeDir(), $this->pieceJointe);
        $this->setPieceJointeFile(null);
    }
}
