<?php
namespace HopitalNumerique\AutodiagBundle\Twig;

use HopitalNumerique\AutodiagBundle\Model\Result\Item;

class RestitutionExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('result_radar_data', array($this, 'getRadarHighChartData')),
            new \Twig_SimpleFunction('result_histogramme_data', array($this, 'getHistogrammeHighChartData')),
        );
    }

    /**
     * @param $result
     * @return string
     */
    public function getRadarHighChartData($result)
    {
        $data = [
            'chart' => [
                'polar' => true,
                'type' => 'line'
            ],
            'title' => [
                'text' => 'Titre',
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
                'min' =>  0,
            ],
            'tooltip' => [
                'shared' => true,
                'pointFormat' => '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>',
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'y' => 70,
                'layout' => 'vertical',
            ],
            'credits' => [
                'enabled' => false,
            ],
            'series' => [
                'score' => [
                    'name' => 'Score',
                    'data' => [],
                ]
            ]
        ];

        foreach ($result['references'] as $code => $reference) {
            $data['series'][$code] = [
                'name' => $reference,
                'data' => []
            ];
        }

        foreach ($result['items'] as $item) {
            /** @var Item $item */
            $data['xAxis']['categories'][] = $item->getLabel();

            $data['series']['score']['data'][] =$item->getScore()->getValue();
            foreach ($result['references'] as $code => $reference) {
                $itemReferences = $item->getReferences();
                $data['series'][$code]['data'][] =
                    isset($itemReferences[$code]) ? $itemReferences[$code]->getValue() : 0;
            }
        }

        $data['series'] = array_values($data['series']);

        return json_encode($data);
    }

    /**
     * @param $result
     * @return string
     */
    public function getHistogrammeHighChartData($result)
    {
        $data = [
            'chart' => [
                'type' => 'bar'
            ],
            'title' => [
                'text' => 'Titre',
            ],
            'xAxis' => [
                'categories' => [],
                'title' => [
                    'text' => null
                ]
            ],
            'yAxis' => [
                'min' => 0,
                'title' => [
                    'text' => 'Score',
                    'align' => 'high',
                ],
                'labels' => [
                    'overflow' => 'justify',
                ]
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'x' => -40,
                'y' => 80,
                'layout' => 'vertical',
                'floating' => true,
                'borderWidth' => 1,
                'shadow' => true,
            ],
            'credits' => [
                'enabled' => false,
            ],
            'series' => [
                'score' => [
                    'name' => 'Score',
                    'data' => [],
                ]
            ]
        ];

        foreach ($result['references'] as $code => $reference) {
            $data['series'][$code] = [
                'name' => $reference,
                'data' => []
            ];
        }

        foreach ($result['items'] as $item) {
            /** @var Item $item */
            $data['xAxis']['categories'][] = $item->getLabel();

            $data['series']['score']['data'][] =$item->getScore()->getValue();
            foreach ($result['references'] as $code => $reference) {
                $itemReferences = $item->getReferences();
                $data['series'][$code]['data'][] =
                    isset($itemReferences[$code]) ? $itemReferences[$code]->getValue() : 0;
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
