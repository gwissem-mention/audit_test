<?php

namespace HopitalNumerique\FichierBundle\Service;

use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\UserBundle\Entity\User;

class FilePathFinder
{
    /**
     * @var string $uploadDir
     */
    protected $uploadDir;

    /**
     * FilePathFinder constructor.
     *
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->uploadDir = sprintf('%s/../files/fichiers', $rootDir);
    }

    /**
     * Get user upload dir
     *
     * @param User $user
     *
     * @return string
     */
    public function getUserFolderPath(User $user)
    {
        return sprintf('%s/%d', $this->uploadDir, $user->getId());
    }

    /**
     * Get complete file path
     *
     * @param File $file
     *
     * @return string
     */
    public function getFilePath(File $file)
    {
        return sprintf('%s/%d/%s', $this->uploadDir, $file->getOwner()->getId(), $file->getName());
    }
}
