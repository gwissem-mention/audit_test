<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\NumberColumn as ApyColumn;

/**
 * Type de colonne Number
 */
class NumberColumn extends ApyColumn
{
    /**
     * Crée une colonne de type Number
     *
     * @param string $field Champ correspondant à cette colonne
     * @param string $title Titre affiché sur le header du grid
     */
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
