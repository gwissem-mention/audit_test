<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\ArrayColumn as ApyColumn;

/**
 * Type de colonne Array
 */
class ArrayColumn extends ApyColumn
{
    /**
     * Créer une colonne de type Array
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
