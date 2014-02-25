<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\BooleanColumn as ApyColumn;

class BooleanColumn extends ApyColumn
{
    public function __construct($field, $title)
    {
        parent::__construct(array(
            'id'         => $field,
            'field'      => $field,
            'title'      => $title,
            'sortable'   => false,
            'source'     => true,
            'filterable' => true,
            'sortable'   => true
        ));
    }

    public function renderCell($value, $row, $router)
    {
        $value = parent::renderCell($value, $row, $router);
        $val   = $this->getValues();
        
        return $value == $val[1] ? 'true' : 'false';
    }
}