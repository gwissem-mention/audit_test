<?php
namespace Nodevo\Component\Import\Writer;

use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Doctrine\Common\Persistence\ObjectManager;

class DoctrineWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    protected $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function prepare()
    {

    }

    public function write($item)
    {
        $this->manager->persist($item);
        $this->manager->flush();
    }

    public function end()
    {

    }

    public function support($item)
    {
        return true;
    }
}
