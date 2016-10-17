<?php
namespace Nodevo\Component\Import\Reader;

interface ReaderInterface
{
    /**
     * Must return a iterable value
     *
     * @param $input
     * @return mixed
     */
    public function read($input);

    public function support($input);
}
