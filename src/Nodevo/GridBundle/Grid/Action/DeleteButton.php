<?php

namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class DeleteButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters(['id']);
        $this->setAttributes(['class' => 'btn btn-danger fa fa-trash-o', 'title' => 'Supprimer']);
    }
}
