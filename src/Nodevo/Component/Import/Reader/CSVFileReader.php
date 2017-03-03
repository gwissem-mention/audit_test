<?php

namespace Nodevo\Component\Import\Reader;

use Nodevo\Component\Import\Iterator\File\KeyedCsvFileIterator;
use Nodevo\Component\Import\Progress\Progress;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Symfony\Component\HttpFoundation\File\File;

class CSVFileReader implements ReaderInterface, ProgressAwareInterface
{
    protected $progress;

    protected $delimiter = ',';
    protected $enclosure = '"';

    public function __construct($delimiter = ',', $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    public function read($file)
    {
        /** @var File $file */
        $file = $file->openFile();
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD);
        $file->setCsvControl(
            $this->delimiter,
            $this->enclosure
        );

        $iterator = new KeyedCsvFileIterator($file);

        return $iterator;
    }

    public function support($input)
    {
        return $input instanceof \SplFileInfo;
    }

    public function setProgress(Progress $progress)
    {
        $this->progress = $progress;
    }
}
