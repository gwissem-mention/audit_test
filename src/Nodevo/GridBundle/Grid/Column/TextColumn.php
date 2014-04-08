<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\TextColumn as ApyColumn;

/**
 * Type de colonne Texte
 */
class TextColumn extends ApyColumn
{
    /**
     * Crée une colonne de type Texte
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
