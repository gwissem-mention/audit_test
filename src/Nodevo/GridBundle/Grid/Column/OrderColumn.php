<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\NumberColumn as ApyColumn;

/**
 * Type de colonne Ordre
 */
class OrderColumn extends ApyColumn
{
    /**
     * Crée une colonne de type Ordre
     */
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
