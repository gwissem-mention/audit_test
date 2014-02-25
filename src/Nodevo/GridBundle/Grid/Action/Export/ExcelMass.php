<?php
namespace Nodevo\GridBundle\Grid\Action\Export;

use APY\DataGridBundle\Grid\Action\MassAction;

class ExcelMass extends MassAction
{
    public function __construct($action)
    {
        parent::__construct('Export Excel', $action);
    }
}