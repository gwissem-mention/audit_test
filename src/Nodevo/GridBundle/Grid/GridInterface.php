<?php
namespace Nodevo\GridBundle\Grid;

/**
 * Interface Grid
 */
interface GridInterface
{
    public function setConfig();
    public function setColumns();
    public function setActionsButtons();
    public function setMassActions();
} 