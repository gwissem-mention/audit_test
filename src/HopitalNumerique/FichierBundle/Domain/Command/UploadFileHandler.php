<?php

namespace HopitalNumerique\FichierBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\FichierBundle\Service\FilePathFinder;

class UploadFileHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var FilePathFinder $filePathFinder
     */
    protected $filePathFinder;

    /**
     * UploadFileHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FilePathFinder $filePathFinder
     */
    public function __construct(EntityManagerInterface $entityManager, FilePathFinder $filePathFinder)
    {
        $this->entityManager = $entityManager;
        $this->filePathFinder = $filePathFinder;
    }

    /**
     * @param UploadFileCommand $command
     *
     * @return File
     */
    public function handle(UploadFileCommand $command)
    {
        $user = $command->owner;
        $uploadedFile = $command->file;

        $uploadedFileName = sprintf('%s.%s', $uploadedFile->getFilename(), $uploadedFile->getClientOriginalExtension());

        $uploadedFile->move(
            $this->filePathFinder->getUserFolderPath($user),
            $uploadedFileName
        );

        $file = new File($uploadedFile->getClientOriginalName(), $uploadedFileName, $user);

        $this->entityManager->persist($file);
        $this->entityManager->flush($file);

        return $file;
    }
}
