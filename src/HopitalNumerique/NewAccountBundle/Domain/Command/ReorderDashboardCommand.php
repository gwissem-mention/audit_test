<?php

namespace HopitalNumerique\NewAccountBundle\Domain\Command;

use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;

/**
 * Class ReorderDashboardCommand
 */
class ReorderDashboardCommand
{
    /**
     * @var string 
     */
    protected $type;

    /**
     * @var array $positions
     * ['id' => X, 'col' => N, 'row' => N], 'visible' => bool][...]
     */
    protected $positions;

    /**
     * @var SettingsOwnerInterface $user
     */
    protected $user;

    /**
     * ReorderDashboardCommand constructor.
     *
     * @param string $type
     * @param array $positions
     * @param SettingsOwnerInterface $user
     */
    public function __construct($type, array $positions, SettingsOwnerInterface $user)
    {
        $this->type = $type;
        $this->positions = $positions;
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @return SettingsOwnerInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
