<?php

namespace Nodevo\Component\Import\Writer;

interface WriterInterface
{
    public function prepare();

    public function write($item);

    public function support($item);

    public function end();
}
