<?php
namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\MassAction;

class DeleteMass extends MassAction
{
    public function __construct($action)
    {
        parent::__construct('Supprimer', $action);
    }
}