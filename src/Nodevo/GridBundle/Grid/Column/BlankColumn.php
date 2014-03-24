<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\BlankColumn as ApyColumn;

class BlankColumn extends ApyColumn
{
    public function __construct($field)
    {
        parent::__construct(array(
            'id'         => $field,
            'field'      => $field,
            'sortable'   => false,
            'source'     => false,
            'filterable' => false,
            'visible'    => false
        ));
    }
}