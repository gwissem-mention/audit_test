<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\NumberColumn as ApyColumn;

class OrderColumn extends ApyColumn
{
    public function __construct()
    {
        parent::__construct(array(
            'id'         => 'order',
            'field'      => 'order',
            'title'      => 'Ordre',
            'sortable'   => true,
            'source'     => true,
            'filterable' => false,
            'size'       => 80,
            'align'      => 'center'
        ));
    }
}