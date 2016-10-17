<?php
namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Nodevo\Component\Import\Writer\WriterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AlgorithmWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    const COLUMN_ALGORITHM = "algorithme";

    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    public function __construct(EntityManager $manager, Autodiag $autodiag)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
    }

    public function prepare()
    {

    }

    public function write($item)
    {
        if ($this->validate($item)) {

            $this->autodiag->setAlgorithm($item[self::COLUMN_ALGORITHM]);

            $currentColumn = 1;
            $referenceNumber = 1;
            $importedReferences = [];
            while ($currentColumn + 3 <= count($item)) {
                $reference = array_values(array_slice($item, $currentColumn, 3));
                if (isset($reference[0]) || isset($reference[1]) || isset($reference[2])) {
                    if ($this->validateReference($reference)) {
                        $model = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Reference')
                            ->findOneBy([
                                'autodiag' => $this->autodiag,
                                'number' => $referenceNumber
                            ]);

                        if (null === $model) {
                            $model = new Autodiag\Reference($referenceNumber, $this->autodiag);
                        }
                        $model
                            ->setLabel($reference[0])
                            ->setValue($reference[1])
                            ->setColor($reference[2]);
                        $this->manager->persist($model);
                        $importedReferences[$referenceNumber] = true;
                    } else {
                        $this->progress->addMessage('', $reference, 'reference_invalid');
                    }
                }
                $currentColumn += 3;
                $referenceNumber++;
            }

            foreach ($this->autodiag->getReferences() as $reference) {
                if (!array_key_exists($reference->getNumber(), $importedReferences)) {
                    $this->manager->remove($reference);
                }
            }

            $this->progress->addSuccess($item);
        } else {
            $this->progress->addError('ad.import.chapter.incorrect_row_format');
        }
    }

    public function support($item)
    {
        return is_array($item);
    }

    public function end()
    {
        $this->manager->flush();
    }

    /**
     * Validate item line
     *
     * @param $item
     * @return bool
     */
    protected function validate($item)
    {
        return
            count($item) >= 1
            && in_array($item[self::COLUMN_ALGORITHM], [
                'moyenne'
            ])
        ;
    }

    protected function validateReference(&$reference)
    {
        $allowed = [
            'moyenne',
            'mediane',
            'decile1',
            'decile1',
            'decile2',
            'decile3',
            'decile4',
            'decile5',
            'decile6',
            'decile7',
            'decile8',
            'decile9',
        ];

        $value = $reference[1];
        if (!in_array($value, $allowed)) {

            preg_match('/[0-9]*[,\.]?[0-9]*/', $value, $matches);
            if (empty($matches)) {
                return false;
            } else {
                $reference[1] = round((float) str_replace(',', '.', $reference[1]), 2);
            }
        }

        return count($reference) === 3
            && !empty($reference[0])
            && !empty($reference[1])
            && !empty($reference[2])
        ;
    }
}
