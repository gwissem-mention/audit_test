<?php

namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Restitution;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Nodevo\Component\Import\Writer\WriterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RestitutionWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    const COLUMN_CATEGORY_LABEL = 'libelle_onglet';
    const COLUMN_CATEGORY_DESCRIPTION = 'texte_avant';
    const COLUMN_CATEGORY_ORDER = 'ordre_affichage_onglet';

    const COLUMN_ITEM_ORDER = 'ordre_affichage_contenu';
    const COLUMN_ITEM_TYPE = 'type_restitution';
    const COLUMN_ITEM_PRIORITY = 'ordre_restitution';
    const COLUMN_ITEM_SOURCE = 'axe_restitution';
    const COLUMN_ITEM_DATA = 'donnees';
    const COLUMN_ITEM_REFERENCES = '/afficher_reference_([0-9]*)/';

    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var Restitution */
    protected $restitution;

    /** @var array|Restitution\Category[] */
    protected $categories = [];

    public function __construct(EntityManager $manager, Autodiag $autodiag, ValidatorInterface $validator)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
        $this->validator = $validator;
    }

    public function prepare()
    {
        $restitution = $this->autodiag->getRestitution();
        if (null === $restitution) {
            $restitution = new Restitution();
            $this->autodiag->setRestitution($restitution);
        }
        $this->restitution = $restitution;

        foreach ($restitution->getCategories() as $category) {
            $this->manager->remove($category);
        }
        $this->manager->flush();
    }

    public function write($item)
    {
        if ($this->validate($item)) {
            $category = $this->handleCategory($item);
            $restitutionItem = $this->handleItem($item, $category);
            if (null !== $restitutionItem) {
                $this->handleReferences($item, $restitutionItem);

                $violations = $this->validator->validate($restitutionItem);
                if (count($violations) > 0) {
                    $this->progress->addMessage(
                        '',
                        $violations,
                        'violation',
                        'restitution'
                    );
                    $this->manager->detach($restitutionItem);

                    return;
                }
            }

            $violations = $this->validator->validate($category);
            if (count($violations) > 0) {
                $this->progress->addMessage(
                    '',
                    $violations,
                    'violation',
                    'restitution'
                );
                $this->manager->detach($category);

                return;
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

    protected function handleCategory($item)
    {
        $category = null;
        if (isset($this->categories[$item[self::COLUMN_CATEGORY_LABEL]])) {
            $category = $this->categories[$item[self::COLUMN_CATEGORY_LABEL]];
        }

        if (null === $category) {
            $category = new Restitution\Category();
            $category
                ->setLabel($item[self::COLUMN_CATEGORY_LABEL])
            ;
            $category->setRestitution($this->restitution);
            $this->categories[$category->getLabel()] = $category;
            $this->manager->persist($category);
        }

        $category
            ->setDescription($item[self::COLUMN_CATEGORY_DESCRIPTION])
            ->setPosition($item[self::COLUMN_CATEGORY_ORDER])
        ;

        return $category;
    }

    protected function handleItem($item, Restitution\Category $category)
    {
        $restitutionItem = new Restitution\Item();
        $restitutionItem
            ->setCategory($category)
            ->setType($item[self::COLUMN_ITEM_TYPE])
            ->setPriority($item[self::COLUMN_ITEM_PRIORITY])
        ;

        switch ($item[self::COLUMN_ITEM_SOURCE]) {
            case 'chapitres':
                $repository = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Chapter');
                $field = 'code';
                break;
            case 'categories':
                $repository = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Category');
                $field = 'label';
                break;
            default:
                $this->progress->addMessage('', $item[self::COLUMN_ITEM_SOURCE], 'source_invalid');

                return null;
        }

        $position = explode('::', $item[self::COLUMN_ITEM_ORDER]);

        if (isset($position[0])) {
            $restitutionItem->setRow((int) $position[0]);
        }
        if (isset($position[1])) {
            $restitutionItem->setColumn((int) $position[1]);
        }

        $identifiers = explode('::', $item[self::COLUMN_ITEM_DATA]);
        $identifiers = array_unique($identifiers);
        foreach ($identifiers as $id) {
            $container = $repository->findOneBy([
                'autodiag' => $this->autodiag,
                $field => $id,
            ]);
            if (null === $container) {
                $this->progress->addMessage('', $id, 'container_identifier_notfound');
            } else {
                $restitutionItem->addContainer($container);
            }
        }

        if (count($restitutionItem->getContainers()) > 0) {
            $this->manager->persist($restitutionItem);

            return $restitutionItem;
        }

        return null;
    }

    protected function handleReferences($item, Restitution\Item $restitutionItem)
    {
        foreach ($item as $key => $value) {
            if (preg_match(self::COLUMN_ITEM_REFERENCES, $key, $matches) && (bool) $value) {
                $referenceNumber = (int) $matches[1];
                $reference = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Reference')
                    ->findOneBy([
                        'autodiag' => $this->autodiag,
                        'number' => $referenceNumber,
                    ]);

                if ($reference) {
                    $restitutionItem->addReference($reference);
                }
            }
        }
    }

    /**
     * Validate item line.
     *
     * @param $item
     *
     * @return bool
     */
    protected function validate($item)
    {
        return
            count($item) >= 8
            && count(array_intersect_key($item, [
                'libelle_onglet' => true,
                'ordre_affichage_onglet' => true,
                'ordre_affichage_contenu' => true,
                'texte_avant' => true,
                'type_restitution' => true,
                'axe_restitution' => true,
                self::COLUMN_ITEM_DATA => true,
                'ordre_restitution' => true,
            ])) === 8
        ;
    }

    protected function validateReference($reference)
    {
        return count($reference) === 3
            && null !== $reference[0]
            && null !== $reference[1]
            && null !== $reference[2]
        ;
    }
}
