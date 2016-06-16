<?php
namespace Nodevo\Component\Import;

use Nodevo\Component\Import\Processor\ItemProcessorInterface;
use Nodevo\Component\Import\Progress\Progress;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressInterface;
use Nodevo\Component\Import\Reader\ReaderInterface;
use Nodevo\Component\Import\Writer\WriterInterface;

class DataImporter
{
    /** @var ReaderInterface */
    protected $reader;

    /** @var WriterInterface */
    protected $writer;

    /** @var ItemProcessorInterface */
    protected $itemProcessor;

    /** @var Progress */
    protected $progress;

    public function __construct(ReaderInterface $reader, WriterInterface $writer = null, ProgressInterface $progress = null)
    {
        $this->reader = $reader;
        $this->writer = $writer;

        if (null === $progress) {
            $progress = new Progress();
        }

        $this->progress = $progress;

        if ($this->reader instanceof ProgressAwareInterface) {
            $this->reader->setProgress($this->progress);
        }

        if ($this->writer instanceof ProgressAwareInterface) {
            $this->writer->setProgress($this->progress);
        }
    }

    public function setWriter(WriterInterface $writer)
    {
        $this->writer = $writer;

        if ($this->writer instanceof ProgressAwareInterface) {
            $this->writer->setProgress($this->progress);
        }
    }

    public function setItemProcessor(ItemProcessorInterface $itemProcessor)
    {
        $this->itemProcessor = $itemProcessor;
        if ($this->itemProcessor instanceof ProgressAwareInterface) {
            $this->itemProcessor->setProgress($this->progress);
        }
    }

    public function import($input)
    {
        if (!$this->writer instanceof WriterInterface) {
            throw new \Exception('You must set a Writer class by calling setWriter method.');
        }

        if (!$this->reader->support($input)) {
            throw new \Exception('Reader does not support this type.');
        }

        $this->progress->start();
        $this->writer->prepare();

        $iterator = $this->reader->read($input);
        foreach ($iterator as $key => $data) {

            $this->progress->setCurrentIndex($key);
            $this->progress->setCurrentItem($data);

            try {
                $this->writer->write(
                    $this->itemProcessor ? $this->itemProcessor->process($data) : $data
                );
            } catch (\Exception $e) {
                $this->progress->addError($e->getMessage());
                continue;
            }
        }

        $this->writer->end();
        $this->progress->end();

        return $this->progress;
    }

}
