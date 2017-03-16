<?php

namespace HopitalNumerique\AutodiagBundle\Twig;

use HopitalNumerique\AutodiagBundle\Model\Result\ComparedScore;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;

class RestitutionExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('result_radar_data', [$this, 'getRadarHighChartData']),
            new \Twig_SimpleFunction('result_histogramme_data', [$this, 'getHistogrammeHighChartData']),
        ];
    }

    /**
     * @param $result
     *
     * @return string
     */
    public function getRadarHighChartData($result)
    {
        $themeButton = new \stdClass();
        $themeButton->class = 'exporting-button';

        $data = [
            'chart' => [
                'polar' => true,
                'type' => 'line',
            ],
            'title' => [
                'text' => false,
            ],
            'pane' => [
                'size' => '80%',
            ],
            'xAxis' => [
                'categories' => [],
                'tickmarkPlacement' => 'on',
                'lineWidth' => 0,
            ],
            'yAxis' => [
                'gridLineInterpolation' => 'polygon',
                'lineWidth' => 0,
                'min' => 0,
            ],
            'tooltip' => [
                'shared' => true,
                'pointFormat' => '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>',
            ],
            'credits' => [
                'enabled' => false,
            ],
            'exporting' => [
                'buttons' => [
                    'contextButton' => [
                        'text' => 'Télécharger le graphique',
                        'menuItems' => null,
                        'theme' => $themeButton,
                    ],
                ],
            ],
            'series' => [
                'score' => [
                    'name' => 'Score',
                    'data' => [],
                ],
            ],
        ];

        foreach ($result['items'] as $item) {
            /* @var Item $item */
            $data['xAxis']['categories'][] = $item->getLabel();

            $data['series']['score']['name'] = $item->getScore()->getLabel();
            $data['series']['score']['data'][] = $item->getScore()->getValue();
            $data['series']['score']['color'] = $item->getScore()->getColor();

            foreach ($item->getReferences() as $reference) {
                $code = $reference->getCode();
                if (!isset($data['series'][$code])) {
                    $data['series'][$code] = [
                        'name' => $reference->getLabel(),
                        'data' => [],
                        'color' => $reference->getColor(),
                    ];
                }

                $data['series'][$code]['data'][] = $reference->getValue();
            }

            if ($item->getScore() instanceof ComparedScore) {
                if (!isset($data['series']['compare'])) {
                    $data['series']['compare'] = [
                        'name' => $item->getScore()->getReference()->getLabel(),
                        'data' => [],
                        'color' => $item->getScore()->getReference()->getColor(),
                    ];
                }

                $data['series']['compare']['data'][] = $item->getScore()->getReference()->getValue();
            }
        }

        $data['series'] = array_values($data['series']);

        return json_encode($data);
    }

    /**
     * @param $result
     *
     * @return string
     */
    public function getHistogrammeHighChartData($result)
    {
        $themeButton = new \stdClass();
        $themeButton->class = 'exporting-button';

        $data = [
            'chart' => [
                'type' => 'bar',
            ],
            'title' => [
                'text' => false,
            ],
            'xAxis' => [
                'categories' => [],
                'title' => [
                    'text' => null,
                ],
            ],
            'yAxis' => [
                'min' => 0,
                'title' => [
                    'text' => 'Score',
                    'align' => 'high',
                ],
                'labels' => [
                    'overflow' => 'justify',
                ],
            ],
            'credits' => [
                'enabled' => false,
            ],
            'series' => [
                'score' => [
                    'name' => 'Score',
                    'data' => [],
                ],
            ],
            'exporting' => [
                'buttons' => [
                    'contextButton' => [
                        'text' => 'Télécharger le graphique',
                        'menuItems' => null,
                        'theme' => $themeButton,
                    ],
                ],
            ],
        ];

        foreach ($result['items'] as $item) {
            /* @var Item $item */
            $data['xAxis']['categories'][] = $item->getLabel();

            $data['series']['score']['name'] = $item->getScore()->getLabel();
            $data['series']['score']['data'][] = $item->getScore()->getValue();
            $data['series']['score']['color'] = $item->getScore()->getColor();

            foreach ($item->getReferences() as $reference) {
                $code = $reference->getCode();
                if (!isset($data['series'][$code])) {
                    $data['series'][$code] = [
                        'name' => $reference->getLabel(),
                        'data' => [],
                        'color' => $reference->getColor(),
                    ];
                }

                $data['series'][$code]['data'][] = $reference->getValue();
            }

            if ($item->getScore() instanceof ComparedScore) {
                if (!isset($data['series']['compare'])) {
                    $data['series']['compare'] = [
                        'name' => $item->getScore()->getReference()->getLabel(),
                        'data' => [],
                        'color' => $item->getScore()->getReference()->getColor(),
                    ];
                }

                $data['series']['compare']['data'][] = $item->getScore()->getReference()->getValue();
            }
        }

        $data['series'] = array_values($data['series']);

        return json_encode($data);
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_restitution';
    }
}
