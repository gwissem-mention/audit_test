<?php

namespace HopitalNumerique\NewAccountBundle\Domain\Command;

use Dmishh\SettingsBundle\Manager\SettingsManagerInterface;

/**
 * Class ReorderDashboardHandler
 */
class ReorderDashboardHandler
{
    /**
     * @var SettingsManagerInterface $settingsManager
     */
    protected $settingsManager;

    /**
     * ReorderDashboardHandler constructor.
     *
     * @param SettingsManagerInterface $settingsManager
     */
    public function __construct(SettingsManagerInterface $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * @param ReorderDashboardCommand $command
     */
    public function handle(ReorderDashboardCommand $command)
    {
        $positions = [];
        foreach ($command->getPositions() as $brick) {
            $positions[$brick['type']][$brick['id']] = $brick['row'] * 2 + $brick['col'];
        }

        $this->settingsManager->set('account_dashboard_order', $positions, $command->getUser());
    }
}
