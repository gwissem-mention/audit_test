<?php

namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class ShowButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters(['id']);
        $this->setAttributes(['class' => 'btn btn-success fa fa-eye', 'title' => 'Afficher']);
    }
}
