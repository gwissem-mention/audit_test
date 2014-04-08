<?php

namespace Nodevo\GridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column as ApyColumn;

/**
 * Type de colonne Locked (icone du verrou)
 */
class LockedColumn extends ApyColumn
{
    /**
     * Crée une colonne de type vérouillé
     */
    public function __construct()
    {
        parent::__construct(array(
            'id'               => 'lock',
            'field'            => 'lock',
            'title'            => 'Vérrouillé',
            'sortable'         => true,
            'source'           => true,
            'filterable'       => true,
            'align'            => 'center',
            'size'             => 90,
            'filter'           => 'select',
            'selectFrom'       => 'values',
            'operatorsVisible' => false
        ));

        $this->setValues( array( 1 => 'Vérrouillé', 0 => 'Non Vérrouillé') );
    }

    public function getType()
    {
        return 'locked';
    }
}
