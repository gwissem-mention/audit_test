<?php

namespace HopitalNumerique\NewAccountBundle\Domain\Command;

use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;

/**
 * Class ReorderDashboardCommand
 */
class ReorderDashboardCommand
{
    /**
     * @var array $positions
     * ['id' => X, 'col' => N, 'row' => N][...]
     */
    protected $positions;

    /**
     * @var SettingsOwnerInterface $user
     */
    protected $user;

    /**
     * ReorderDashboardCommand constructor.
     *
     * @param array $positions
     * @param SettingsOwnerInterface $user
     */
    public function __construct(array $positions, SettingsOwnerInterface $user)
    {
        $this->positions = $positions;
        $this->user = $user;
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
