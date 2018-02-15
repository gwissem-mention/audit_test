<?php

namespace HopitalNumerique\DocumentBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use HopitalNumerique\DocumentBundle\Entity\Document;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Add File instance on Document entity on load.
 */
class FileSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected $targetDir;

    /**
     * @param $targetDir
     */
    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
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
