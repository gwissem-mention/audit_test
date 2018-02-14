<?php

namespace HopitalNumerique\DocumentBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use HopitalNumerique\DocumentBundle\Entity\Document;
use Symfony\Component\HttpFoundation\File\File;

class FileSubscriber implements EventSubscriber
{
    protected $targetDir;

    /**
     * SearchIndexerSubscriber constructor.
     * @param $targetDir
     */
    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Document) {
            return;
        }

        if ($fileName = $entity->getFilename()) {
            $entity->setFile(new File($this->targetDir . '/' . $fileName));
        }
    }
}
