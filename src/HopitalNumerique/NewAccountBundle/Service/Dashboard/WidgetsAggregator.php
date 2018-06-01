<?php

namespace HopitalNumerique\NewAccountBundle\Service\Dashboard;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use Dmishh\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class WidgetsAggregator
 */
class WidgetsAggregator
{
    /**
     * @var WidgetInterface[]|array $widgets
     */
    protected $widgets = [];

    /**
     * @var WidgetInterface[]|array $widgets
     */
    protected $sorted = [];

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var SettingsManagerInterface $settingsManager
     */
    protected $settingsManager;

    /**
     * @var TokenStorageInterface $authenticationToken
     */
    protected $authenticationToken;

    /**
     * WidgetsAggregator constructor.
     *
     * @param TranslatorInterface $translator
     * @param SettingsManagerInterface $settingsManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        TranslatorInterface $translator,
        SettingsManagerInterface $settingsManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param WidgetInterface $widget
     * @param string          $type
     * @param int             $priority
     */
    public function addWidget(WidgetInterface $widget, $type, $priority = 0)
    {
        $this->widgets[$type][$priority][] = $widget;
        unset($this->sorted[$type]);
    }

    /**
     * @param string         $type
     * @param Domaine[]|null $domains
     *
     * @return Widget[]
     */
    public function getWidgets($type, $domains = null)
    {
        $widgets = [];

        foreach ($this->getWidgetFactories($type) as $widgetCreator) {
            if (null !== $domains && $widgetCreator instanceof DomainAwareInterface) {
                $widgetCreator->setDomains($domains);
            }

            if (null !== ($widgetToAdd = $widgetCreator->getWidget())) {
                $widgets[] = $widgetToAdd;
            }
        }

        $positions = $this->settingsManager->get('account_dashboard_order', $this->tokenStorage->getToken()->getUser());

        if (isset($positions[$type])) {
            $typedPositions = $positions[$type];
            usort($widgets, function (Widget $a, Widget $b) use ($typedPositions) {
                if (!isset($typedPositions[$a->getName()]['position']) || !isset($typedPositions[$b->getName()]['position'])) {
                    return 1;
                }

                return $typedPositions[$a->getName()]['position'] - $typedPositions[$b->getName()]['position'];
            });
        }

        return $widgets;
    }

    /**
     * @param null $type
     *
     * @return array|WidgetInterface|WidgetInterface[]
     */
    public function getWidgetFactories($type = null)
    {
        if (null !== $type) {
            if (!isset($this->widgets[$type])) {
                return [];
            }

            if (!isset($this->sorted[$type])) {
                $this->sortWidgetFactories($type);
            }

            return $this->sorted[$type];
        }

        foreach ($this->widgets as $type => $widgetFactory) {
            if (!isset($this->sorted[$type])) {
                $this->sortWidgetFactories($type);
            }
        }

        return array_filter($this->sorted);
    }

    /**
     * @param $type
     */
    private function sortWidgetFactories($type)
    {
        krsort($this->widgets[$type]);
        $this->sorted[$type] = call_user_func_array('array_merge', $this->widgets[$type]);
    }
}
