<?php
namespace HopitalNumerique\AutodiagBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AutodiagAdminFileImport
{
    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @var boolean
     */
    private $notifyUpdate;

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
