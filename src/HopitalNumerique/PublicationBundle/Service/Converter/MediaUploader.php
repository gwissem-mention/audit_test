<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Media;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class MediaUploader
{
    private $publicRootDir;
    private $targetDir;

    /**
     * MediaUploader constructor.
     * @param $publicRootDir
     * @param $targetDir
     */
    public function __construct($publicRootDir, $targetDir)
    {
        $this->publicRootDir = $publicRootDir;
        $this->targetDir = $publicRootDir . $targetDir;
    }

    public function upload(File $file, Objet $publication)
    {
        $fs = new Filesystem();
        if ($fs->exists($file)) {
            $file = $file->move($this->getTargetDir($publication));
        } else {
            $file = new File($this->getTargetDir($publication) . '/' . $file->getFilename());
        }

        return $fs->makePathRelative($file->getPath(), $this->publicRootDir) . $file->getFilename();
    }

    /**
     * Move media to his new name
     *
     * @param Media $media
     * @return Media
     */
    public function moveMedia(Media $media)
    {
        $info = pathinfo($media->getPath());
        $file = new File($this->publicRootDir . $media->getPath());

        if (null !== $media->getName()) {
            $file = $file->move(
                $this->publicRootDir . $info['dirname'],
                $this->getTargerFilename($media)
            );

            $fs = new Filesystem();
            $media->setPath(
                $fs->makePathRelative($file->getPath(), $this->publicRootDir) . $file->getFilename()
            );
        }

        return $media;
    }

    public function removeMedia(Media $media)
    {
        $fs = new Filesystem();
        $fs->remove($this->publicRootDir . $media->getPath());
    }

    private function getTargetDir(Objet $publication)
    {
        $path = $this->targetDir . $publication->getId();

        $fs = new Filesystem();
        $fs->mkdir($path);

        return $path;
    }

    private function getTargerFilename(Media $media)
    {
        $text = $media->getName();

        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            $text = 'n-a';
        }

        $info = pathinfo($media->getPath());
        $baseFilename = $text;
        $targetFilename = sprintf("%s.%s", $text, $info['extension']);

        $iterator = 0;
        $fs = new Filesystem();
        while ($fs->exists($this->publicRootDir . $info['dirname'] . '/' . $targetFilename)) {
            $iterator++;
            $targetFilename = sprintf('%s-%s.%s', $baseFilename, $iterator, $info['extension']);
        }

        return $targetFilename;
    }
}
