<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Restitution;

class RestitutionExport extends AbstractExport
{
    private $referencesCount = 10;

    public function export(Autodiag $autodiag)
    {
        $excel = new \PHPExcel();
        $excel->removeSheetByIndex(0);
        $sheet = $this->addSheet($excel, 'resultat');

        $this->writeHeader($sheet);

        if (null !== $autodiag->getRestitution()) {
            $this->writeCategoryRows($sheet, $autodiag->getRestitution());
        }

        return $this->getFileResponse($excel, $autodiag->getTitle(), 'resultat');
    }

    protected function writeHeader(\PHPExcel_Worksheet $sheet)
    {
        $data = [
            'libelle_onglet',
            'ordre_affichage_onglet',
            'ordre_affichage_contenu',
            'texte_avant',
            'type_restitution',
            'axe_restitution',
            'donnees',
            'ordre_restitution',
        ];

        for ($i = 1; $i <= $this->referencesCount; $i++) {
            $data[] = 'afficher_reference_' . $i;
        }

        $this->addRow($sheet, $data);
    }

    protected function writeCategoryRows(\PHPExcel_Worksheet $sheet, Restitution $restitution)
    {
        $categories = $restitution->getCategories();

        foreach ($categories as $category) {
            /** @var Restitution\Category $category */

            foreach ($category->getItems() as $item) {
                /** @var Restitution\Item $item */
                $row = [
                    $category->getLabel(),
                    $category->getPosition(),
                    $item->getRow() . '::' . $item->getColumn(),
                    $category->getDescription(),
                    $item->getType(),
                ];

                $containers = $item->getContainers();

                if (count($containers) > 0) {
                    $containerCodes = array_map(function ($container) {
                        return $container->getCode();
                    }, $containers);
                    $containerCodes = implode('::', $containerCodes);
                    $row[] = $containers[0] instanceof Autodiag\Container\Chapter ? 'chapitres' : 'categories';
                    $row[] = $containerCodes;
                } else {
                    $row[] = '';
                    $row[] = '';
                }

                $row[] = $item->getPriority();

                $references = $item->getReferences();
                $referencesKeyed = [];
                foreach ($references as $reference) {
                    /** @var Autodiag\Reference $reference */
                    $referencesKeyed[$reference->getNumber()] = $reference;
                }

                for ($i = 1; $i <= $this->referencesCount; $i++) {
                    $row[] = isset($referencesKeyed[$i]) ? '1' : '';
                }

                $this->addRow($sheet, $row);
            }
        }
    }
}
