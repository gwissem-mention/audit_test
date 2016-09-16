<?php

namespace HopitalNumerique\AutodiagBundle\Service\Export;

use Box\Spout\Writer\AbstractMultiSheetsWriter;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use \HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\RestitutionCalculator;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RestitutionItemExport
{
    protected $calculator;
    protected $gabaritPath;

    protected $row = 1;

    public function __construct(RestitutionCalculator $calculator, $gabaritPath)
    {
        $this->calculator = $calculator;
        $this->gabaritPath = $gabaritPath;
    }

    public function export(Synthesis $synthesis, Item $item, User $user, $fileType = 'xlsx')
    {
        /** @var AbstractMultiSheetsWriter $writer */
        $writer = WriterFactory::create($fileType);
        $file = stream_get_meta_data(tmpfile())['uri'];
        $writer->openToFile($file);

        $result = $this->calculator->computeItem($item, $synthesis);

        $this->writeHeader($writer, $synthesis, $user);

        foreach ($result['items'] as $resultItem) {
            $this->writeResultItem($writer, $resultItem);
        }

        $writer->close();

        $title = new Chaine($synthesis->getName());
        $name = 'plan_action_' . $title->minifie() . '.' . $fileType;

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name
        );
        return $response;
    }



    protected function writeHeader(WriterInterface $writer, Synthesis $synthesis, User $user)
    {
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(18)
            ->build();

        $writer->addRowWithStyle([
            sprintf(
                'Autodiagnostic "%s" - "%s"',
                $synthesis->getAutodiag()->getTitle(),
                $synthesis->getName()
            )
        ], $style);


        $style = (new StyleBuilder())
            ->setFontItalic()
            ->build();

        $now = new \DateTime();
        $writer->addRowWithStyle([
            sprintf(
                'Plan d\'action exporté le %s à %s par %s',
                $now->format('d/m/Y'),
                $now->format('H:i'),
                $user->getPrenom() . ' ' . $user->getNom()
            )
        ], $style);


        $style = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor('c0c0c0')
            ->build();

        $writer->addRowWithStyle([
            'Chapitre',
            'Sous chapitre',
            'Question',
            'Réponse',
            'Synthèse',
            'Commentaire',
            'Acteur',
            'Échéance',
            'État d\'avancement',

        ], $style);
    }

    protected function writeResultItem(WriterInterface $writer, ResultItem $item, ResultItem $parent = null)
    {
        $visible = false;

        if (null !== $item->getActionPlan()) {
            $visible = true;
        }

        foreach ($item->getAttributes() as $attribute) {
            if (null !== $attribute->getActionPlan()) {
                $visible = true;
                break;
            }
        }

        foreach ($item->getChildrens() as $children) {
            if (null !== $children->getActionPlan()) {
                $visible = true;
                break;
            }

            foreach ($children->getAttributes() as $attribute) {
                if (null !== $attribute->getActionPlan()) {
                    $visible = true;
                    break;
                }
            }
        }

        if (false === $visible) {
            return null;
        }

        $writer->addRow([
            $parent ? $parent->getLabel() : $item->getLabel(),
            $parent ? $item->getLabel() : '',
            '',
            '',
            $item->getActionPlan() ? $item->getActionPlan()->getDescription() : ''
        ]);

        foreach ($item->getAttributes() as $attribute) {
            if (null !== $attribute->getActionPlan()) {
                $writer->addRow(
                    [
                        $parent ? $parent->getLabel() : $item->getLabel(),
                        $parent ? $item->getLabel() : '',
                        $attribute->label,
                        $attribute->responseText,
                        $attribute->getActionPlan() ? $attribute->getActionPlan()->getDescription() : ''
                    ]
                );
            }
        }

        foreach ($item->getChildrens() as $children) {
            $this->writeResultItem($writer, $children, $item);
        }
    }
}
