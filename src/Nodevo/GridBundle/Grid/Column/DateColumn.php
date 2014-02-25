<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\DateTimeColumn as ApyColumn;

class DateColumn extends ApyColumn
{
    public function __construct($field, $title, $format = "d/m/Y")
    {
        parent::__construct(array(
            'id'         => $field,
            'field'      => $field,
            'title'      => $title,
            'sortable'   => true,
            'source'     => true,
            'filterable' => true,
            'format'     => $format,
            'size'       => 130
        ));
    }
}