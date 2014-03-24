<?php

namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\BooleanColumn as ApyColumn;

class LockedColumn extends ApyColumn
{
    public function __construct()
    {
        parent::__construct(array(
            'id'         => 'lock',
            'field'      => 'lock',
            'title'      => 'Vérrouillé',
            'sortable'   => false,
            'source'     => true,
            'filterable' => false,
            'align'      => 'center',
            'size'       => 80
        ));

        $this->setValues( array( 1 => 'Vérrouillé', 0 => 'Non Vérrouillé') );
    }

    public function renderCell($value, $row, $router)
    {
        $value = parent::renderCell($value, $row, $router);

        return $value == "Vérrouillé" ? 'true' : 'false';
    }
}