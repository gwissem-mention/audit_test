<?php

namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class FancyButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters(['id']);
        $this->setAttributes(['class' => 'btn btn-primary fancy fancybox.ajax fa fa-cogs', 'title' => 'Ouvrir la pop-in']);
    }
}
