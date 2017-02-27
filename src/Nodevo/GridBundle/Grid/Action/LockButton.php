<?php

namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class LockButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters(['id']);

        $this->manipulateRender(function (RowAction $action, \APY\DataGridBundle\Grid\Row $row) {
            if ($row->getField('lock')) {
                $action->setAttributes(['class' => 'btn btn-warning fa fa-lock', 'title' => 'Verrouiller']);
            } else {
                $action->setAttributes(['class' => 'btn btn-warning fa fa-unlock', 'title' => 'Déverrouiller']);
            }

            return $action;
        });
    }
}
