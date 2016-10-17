<?php
namespace Nodevo\Component\Import\Iterator\File;

class KeyedExcelFileIterator implements \Iterator
{
    private $keys;
    private $sheet;

    private $currentColumn;
    private $currentRow;
    private $highestColumn;
    private $highestRow;

    public function __construct(\PHPExcel_Worksheet $sheet)
    {
        $this->sheet = $sheet;

        $this->highestRow = $sheet->getHighestRow();
        $this->highestColumn = $sheet->getHighestColumn();

        $this->rewind();
    }

    public function rewind()
    {
        $this->currentColumn = 'A';
        $this->currentRow = 1;

        $this->keys = array_filter(
            $this->currentRangeToArray(),
            function ($element) {
                return null !== $element;
            }
        );

        $this->next();
    }

    public function current()
    {
        $row = $this->currentRangeToArray();

        if (!is_array($row)) {
            return null;
        }

        $row = array_combine($this->keys, array_slice($row, 0, count($this->keys)));

        return $row;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    protected function currentRangeToArray()
    {
        return current($this->sheet->rangeToArray(
            'A' . $this->currentRow . ':' . $this->highestColumn . $this->currentRow,
            null,
            true,
            false
        ));
    }

    public function next()
    {
        $this->currentRow++;
    }

    public function key()
    {
        return $this->currentRow;
    }

    public function valid()
    {
        return $this->currentRow > 1 && $this->currentRow <= $this->highestRow;
    }
}
