<?php
namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\BooleanColumn as ApyColumn;

/**
 * Type de colonne Boolean
 */
class BooleanColumn extends ApyColumn
{
    /**
     * Crée une colonne de type boolean
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

        $this->setValues($this->getParam('values', array(1 => 'Oui', 0 => 'Non')));
    }
}
