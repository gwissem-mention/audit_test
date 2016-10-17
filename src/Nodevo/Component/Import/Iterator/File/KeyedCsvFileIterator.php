<?php
namespace Nodevo\Component\Import\Iterator\File;

class KeyedCsvFileIterator extends \IteratorIterator
{
    private $keys;

    public function rewind()
    {
        parent::rewind();
        $this->keys = parent::current();
        parent::next();
    }

    public function current()
    {
        $row = parent::current();

        if (!is_array($row)) {
            return null;
        }

        $arrayLength = count($this->keys);
        if (count($row) > $arrayLength) {
            $row = array_slice($row, 0, $arrayLength);
        } else {
            $row = array_pad($row, $arrayLength, null);
        }

        $row = array_combine($this->keys, $row);

        return $row;
    }

    public function getKeys()
    {
        return $this->keys;
    }
}
