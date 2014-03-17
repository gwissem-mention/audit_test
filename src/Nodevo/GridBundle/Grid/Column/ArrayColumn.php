<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\ArrayColumn as ApyColumn;

class ArrayColumn extends ApyColumn
{
    public function __construct($field, $title)
    {
        parent::__construct(array(
            'id'         => $field,
            'field'      => $field,
            'title'      => $title,
            'sortable'   => true,
            'source'     => true,
            'filterable' => true
        ));
    }
}