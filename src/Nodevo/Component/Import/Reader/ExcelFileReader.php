<?php
namespace Nodevo\Component\Import\Reader;

use Nodevo\Component\Import\Iterator\File\KeyedExcelFileIterator;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class ExcelFileReader implements ReaderInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    protected $sheetNumber = 0;

    public function __construct($sheetNumber)
    {
        $this->sheetNumber = $sheetNumber;
    }

    /**
     * @param File $file
     * @return KeyedExcelFileIterator
     */
    public function read($file)
    {
        $fileReader = $this->getFileReader($file);
        $sheet = $fileReader->getSheet($this->sheetNumber);
        $iterator = new KeyedExcelFileIterator($sheet);

        return $iterator;
    }

    public function support($input)
    {
        return $input instanceof \SplFileInfo;
    }

    /**
     * @param File $file
     * @return \PHPExcel
     * @throws \PHPExcel_Reader_Exception
     */
    protected function getFileReader(File $file)
    {
        $inputFileType = \PHPExcel_IOFactory::identify($file);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        return $objReader->load($file);
    }
}
