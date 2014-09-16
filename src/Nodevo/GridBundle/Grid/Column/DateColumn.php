<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\DateColumn as ApyColumn;

/**
 * Type de colonne Date
 */
class DateColumn extends ApyColumn
{
    /**
     * Crée une colonne de type date
     *
     * @param string $field  Champ correspondant à cette colonne
     * @param string $title  Titre affiché sur le header du grid
     * @param string $format Format de la date affichée
     */
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
