<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\BlankColumn as ApyColumn;

/**
 * Type de Colonne Blank (à ne pas afficher)
 */
class BlankColumn extends ApyColumn
{
    /**
     * Crée une colonne invisible
     *
     * @param string $field Champ correspondant à cette colonne
     */
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
