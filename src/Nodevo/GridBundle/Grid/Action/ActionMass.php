<?php
namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\MassAction;

class ActionMass extends MassAction
{
    public function __construct($title, $action)
    {
        parent::__construct($title, $action);
    }
}