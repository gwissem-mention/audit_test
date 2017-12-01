<?php

namespace HopitalNumerique\FichierBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileCommand
{
    /**
     * @var UploadedFile $file
     */
    public $file;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * UploadFile constructor.
     *
     * @param UploadedFile $file
     * @param User $owner
     */
    public function __construct(UploadedFile $file, User $owner)
    {
        $this->file = $file;
        $this->owner = $owner;
    }
}
