<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\AttributeRepository;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository;
use HopitalNumerique\AutodiagBundle\Service\Result\ResultItemBuilder;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AutodiagEntriesExport extends AbstractExport
{
    const HEADER_HORIZONTAL_OFFSET = 7;

    /**
     * @var ResultItemBuilder
     */
    protected $resultItemBuilder;

    /**
     * @var SynthesisRepository
     */
    protected $synthsisRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var ValueRepository
     */
    protected $valueRepository;

    /**
     * @var Completion
     */
    protected $completion;

    private $attributes;

    public function __construct(ObjectManager $manager, SynthesisRepository $synthesisRepository, AttributeRepository $attributeRepository, ValueRepository $valueRepository, Completion $completion)
    {
        parent::__construct($manager);

        $this->synthsisRepository = $synthesisRepository;
        $this->attributeRepository = $attributeRepository;
        $this->valueRepository = $valueRepository;
        $this->completion = $completion;
    }

    /**
     * @param Autodiag $autodiag
     * @param Synthesis[] $syntheses
     * @return StreamedResponse
     */
    public function exportList(Autodiag $autodiag, $syntheses)
    {
        $response =  new StreamedResponse(function () use ($autodiag, $syntheses) {
            $handle = fopen('php://output', 'r+');
            $this->attributes = $this->writeAttributes($handle, $autodiag);
            $this->writeHeader($handle);
            $this->writeSyntheses($handle, $syntheses);
        });

        $title = new Chaine($autodiag->getTitle());
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition','attachment; filename="' . 'resultat_' .  $title->minifie() . '.csv' . '"');
        return $response;

    }

    protected function writeHeader($handle)
    {
        $head = [
            'Nom de l\'utilisateur',
            'Établissement',
            'Date de création',
            'Dernier enregistrement',
            'Date de validation',
            'Pourcentage de remplissage',
            'Nom donné par l\'utilisateur'
        ];

        $this->writeRow($handle, $head);
    }

    protected function writeAttributes($handle, Autodiag $autodiag)
    {
        $attributes = $this->attributeRepository->getAttributesWithChapter($autodiag);

        $fields = [
            'chapter_parent' => 'Chapitre',
            'chapter' => 'Sous chapitre',
            'attribute_label' => 'Question',
            'weight' => 'Pondération',
        ];

        foreach ($fields as $field => $title) {
            $this->writeRow(
                $handle,
                array_merge(
                    array_fill(0, self::HEADER_HORIZONTAL_OFFSET -1, ''),
                    [$title],
                    call_user_func_array('array_merge', array_map(function ($row) use ($field) {
                        return [
                            $row[$field],
                            $field == 'attribute_label' ? 'Valeur' : '',
                            $field == 'attribute_label' ? 'Commentaire' : '',
                        ];
                    }, $attributes))
                )
            );
        }



        return $attributes;
    }

    protected function writeSyntheses($handle, $synthesisIds)
    {

        foreach ($synthesisIds as $id) {
            $values = $this->valueRepository->getFullValues($id);

            $output = $this->getSynthesisDetails($id);

            foreach ($this->attributes as $attribute) {
                if (isset($values[$attribute['id']])) {
                    $output = array_merge($output, [
                        $values[$attribute['id']]['value_label'],
                        $values[$attribute['id']]['value_value'],
                        $values[$attribute['id']]['value_comment'],
                    ]);
                } else {
                    $output = array_merge($output, array_fill(0, 3, ''));
                }
            }
            $this->writeRow($handle, $output);
        }
    }

    protected function getSynthesisDetails($id)
    {
        $details = $this->synthsisRepository->getSynthesisDetailsForExport($id);

        if (null === $details) {
            return array_fill(0, self::HEADER_HORIZONTAL_OFFSET, '');
        }

        return [
            $details['fullname'],
            $details['etablissement'] ?: $details['autre_etablissement'],
            null !== $details['createdAt'] ? $details['createdAt']->format('d/m/Y') : '',
            null !== $details['updatedAt'] ? $details['updatedAt']->format('d/m/Y') : '',
            null !== $details['validatedAt'] ? $details['validatedAt']->format('d/m/Y') : '',
            $details['completion'],
            $details['name'],
        ];
    }

    protected function writeRow($handle, $data)
    {
        fputcsv(
            $handle,
            $data,
            ';'
        );
    }

}
