<?php
namespace HopitalNumerique\AutodiagBundle\Model;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class AutodiagFileImport
{
    /**
     * @var Autodiag
     */
    private $autodiag;

    /**
     * @var UploadedFile
     * @Assert\NotBlank()
     */
    protected $file;

    /**
     * @var boolean
     */
    private $notifyUpdate;

    public function __construct(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;
    }

    /**
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getNotifyUpdate()
    {
        return $this->notifyUpdate;
    }

    /**
     * @param mixed $notifyUpdate
     */
    public function setNotifyUpdate($notifyUpdate)
    {
        $this->notifyUpdate = $notifyUpdate;
    }
}
