<?php
namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Restitution;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Nodevo\Component\Import\Writer\WriterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class RestitutionWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    const COLUMN_CATEGORY_LABEL = "libelle_onglet";
    const COLUMN_CATEGORY_DESCRIPTION = "texte_avant";
    const COLUMN_CATEGORY_ORDER = "ordre_affichage_onglet";

    const COLUMN_ITEM_TYPE = 'type_restitution';
    const COLUMN_ITEM_PRIORITY = 'ordre_restitution';
    const COLUMN_ITEM_SOURCE = 'axe_restitution';
    const COLUMN_ITEM_DATA = 'donnees';
    const COLUMN_ITEM_REFERENCES = '/afficher_reference_([0-9]*)/';

    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    /** @var Restitution */
    protected $restitution;

    /** @var array|Restitution\Category[] */
    protected $categories = [];

    public function __construct(EntityManager $manager, Autodiag $autodiag)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
//        $this->restitution = $autodiag->getRestitution();
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
            }

            $this->progress->addSuccess($item);
        } else {
            $this->progress->addError('algorithm incorect format');
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
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('label', $item[self::COLUMN_CATEGORY_LABEL]));
        $category = $this->restitution->getCategories()->matching($criteria)->first();

        if (false === $category) {
            $category = new Restitution\Category();
            $category
                ->setLabel($item[self::COLUMN_CATEGORY_LABEL])
            ;
            $this->restitution->addCategory($category);
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
                die('error');
        }

        $identifiers = explode('::', $item[self::COLUMN_ITEM_DATA]);
        foreach ($identifiers as $id) {
            $container = $repository->findOneBy([
                'autodiag' => $this->autodiag,
                $field => $id
            ]);
            if (null === $container) {
                $this->progress->addMessage('', $id, 'container.identifier.notfound');
            } else {
                $restitutionItem->addContainer($container);
            }
        }

        if ($restitutionItem->getContainers()->count() > 0) {
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
                        'number' => $referenceNumber
                    ]);

                if ($reference) {
                    $restitutionItem->addReference($reference);
                }
            }
        }
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
