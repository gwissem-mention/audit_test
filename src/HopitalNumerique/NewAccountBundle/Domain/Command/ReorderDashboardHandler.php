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
        $bricks = $command->getPositions();
        usort($bricks, function ($a, $b) {
            if (0 !== ($a['row'] - $b['row'])) {
                return $a['row'] - $b['row'];
            }

            return $a['col'] - $b['col'];
        });

        $i = 1;
        $positions = [];
        foreach ($bricks as $brick) {
            $positions[$command->getType()][$brick['id']] = [
                'position' => $i++,
                'visible' => array_key_exists('visible', $brick) ? $brick['visible'] : true,
            ];
        }

        $this->settingsManager->set('account_dashboard_order', $positions, $command->getUser());
    }
}
