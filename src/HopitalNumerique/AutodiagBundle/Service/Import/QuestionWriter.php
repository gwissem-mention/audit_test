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

class QuestionWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    const COLUMN_CODE = "code_question";
    const COLUMN_OPTIONS = "items_reponse";
    const COLUMN_CHAPTER = "code_chapitre";
    const COLUMN_CHAPTER_WEIGHT = "ponderation_chapitre";
    const COLUMN_CATEGORIES = "ponderation_categorie";
    const COLUMN_TYPE = "format_reponse";

    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    /** @var AttributeBuilderProvider */
    protected $attributesProvider;

    /** @var array */
    protected $attributeTypesAvailable;

    protected $chapters = [];

    protected $categories = [];

    protected $attributes;

    protected $mapping = [
        'texte_avant' => 'description',
        'libelle_question' => 'label',
        'format_reponse' => 'type',
        'colorer_reponse' => 'colored',
        'infobulle_question' => 'tooltip',
    ];

    public function __construct(EntityManager $manager, Autodiag $autodiag, AttributeBuilderProvider $attributesProvider)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
        $this->attributesProvider = $attributesProvider;

        $this->attributes = new ArrayCollection();

        $this->attributeTypesAvailable = $this->attributesProvider->getBuildersName();
    }

    public function prepare()
    {

    }

    public function write($item)
    {
        if ($this->validate($item)) {
            $attribute = $this->getAttribute($item[self::COLUMN_CODE], $item[self::COLUMN_TYPE]);

            $propertyAccessor = new PropertyAccessor();
            foreach ($this->mapping as $key => $property) {
                if (array_key_exists($key, $item) && null !== $item[$key]) {
                    $propertyAccessor->setValue($attribute, $property, (string)$item[$key]);
                }
            }

            $this->handleOptions($attribute, $item[self::COLUMN_OPTIONS]);

            $this->handleChapter($attribute, $item[self::COLUMN_CHAPTER], $item[self::COLUMN_CHAPTER_WEIGHT]);

            $this->handleCategories($attribute, $item[self::COLUMN_CATEGORIES]);

            $this->manager->persist($attribute);

            $this->progress->addSuccess($item);
        } else {
            $this->progress->addError('chapter incorect format');
        }
    }

    public function support($item)
    {
        return is_array($item);
    }

    public function end()
    {
        $attributes = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute')->findBy([
            'autodiag' => $this->autodiag,
        ]);
        foreach ($attributes as $attribute) {
            if (!$this->attributes->contains($attribute)) {
                $this->manager->remove($attribute);
            }
        }

        $this->manager->flush();
    }

    /**
     * Get an existing attribute or create a new one
     *
     * @param $code
     * @return Attribute
     */
    protected function getAttribute($code, $type)
    {
        $attribute = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute')->findOneBy([
            'autodiag' => $this->autodiag,
            'code' => $code,
            'type' => $type,
        ]);

        if (null === $attribute) {
            $attribute = new Attribute();
            $attribute
                ->setAutodiag($this->autodiag)
                ->setCode($code);
        }

        $this->attributes->add($attribute);

        return $attribute;
    }

    /**
     * Get an existing Option for $attribute or create a new one and append it to $attribute
     *
     * @param Attribute $attribute
     * @param $value
     * @return Attribute\Option
     */
    protected function getOption(Attribute $attribute, $value)
    {
        $found = $attribute->getOptions()->filter(function (Attribute\Option $option) use ($value) {
            return $option->getValue() === (string) $value;
        });

        if ($found->count() > 0) {
            return $found->first();
        }

        $option = new Attribute\Option($attribute, (string) $value);
        $attribute->addOption($option);

        return $option;
    }

    /**
     * Parse multiline cells (key::value\n...)
     *
     * @param $input
     * @return array
     */
    protected function parseMultiline($input)
    {
        $lines = preg_split("/\\r\\n|\\r|\\n/", $input);
        $options = [];

        foreach ($lines as $line) {
            $option = explode('::', $line);
            if (count($option) == 2) {
                $options[trim($option[0])] = trim($option[1]);
            }
        }

        return $options;
    }

    /**
     * Handle attribute options
     *
     * @param Attribute $attribute
     * @param $options
     */
    protected function handleOptions(Attribute $attribute, $options)
    {
        $collection = new ArrayCollection();
        $options = $this->parseMultiline($options);
        foreach ($options as $value => $label) {
            $option = $this->getOption($attribute, $value);
            $option->setLabel($label);
            $collection->add($option);
        }

        foreach ($attribute->getOptions() as $key => $element) {
            $found = $collection->exists(function ($key, Attribute\Option $option) use ($element) {
                return $option->getValue() == $element->getValue();
            });

            if (!$found) {
                // @TODO: Voir la suppression des réponses en cascade
                $attribute->removeOption($element);
            }
        }
    }

    /**
     * Handle attribute chapter and weight
     *
     * @param Attribute $attribute
     * @param $code
     * @param $weight
     */
    protected function handleChapter(Attribute $attribute, $code, $weight)
    {
        $chapter = $this->getChapter($code);
        if (null === $chapter) {
            $this->progress->addMessage('', $code, 'chapter.notfound');
        } else {
            $weightObject = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight')
                ->findOneBy([
                    'attribute' => $attribute,
                    'container' => $chapter
                ]);

            if (null == $weightObject) {
                $weightObject = new Attribute\Weight($chapter, $attribute);
                $this->manager->persist($weightObject);
            }

            $weightObject->setWeight($this->parseFloatValue($weight));
        }
    }

    /**
     * Handle attribute categories and their weight
     *
     * @param Attribute $attribute
     * @param $categoriesData
     */
    protected function handleCategories(Attribute $attribute, $categoriesData)
    {
        $categoriesData = $this->parseMultiline($categoriesData);
        foreach ($categoriesData as $code => $weight) {
            $category = $this->getCategory($code);

            $weightObject = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight')
                ->findOneBy([
                    'attribute' => $attribute,
                    'container' => $category
                ]);

            if (null == $weightObject) {
                $weightObject = new Attribute\Weight($category, $attribute);
                $this->manager->persist($weightObject);
            }

            $weightObject->setWeight($this->parseFloatValue($weight));
        }
    }

    /**
     * Get existing chapter
     *
     * @param $code
     * @return mixed
     */
    protected function getChapter($code)
    {
        if (!array_key_exists((string)$code, $this->chapters)) {
            $chapter = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Chapter')
                ->findOneBy([
                    'autodiag' => $this->autodiag,
                    'code' => $code
                ]);

            $this->chapters[$code] = $chapter;
        }

        return $this->chapters[$code];
    }

    /**
     * Get existing category
     *
     * @param $code
     * @return mixed
     */
    protected function getCategory($code)
    {
        if (!array_key_exists((string)$code, $this->categories)) {
            $category = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Category')
                ->findOneBy([
                    'autodiag' => $this->autodiag,
                    'code' => $code
                ]);

            if (null === $category) {
                $category = new Autodiag\Container\Category();
                $category
                    ->setAutodiag($this->autodiag)
                    ->setCode($code)
                    ->setLabel($code);
                $this->manager->persist($category);
            }

            $this->categories[$code] = $category;
        }

        return $this->categories[$code];
    }

    /**
     * Validate item line
     *
     * @param $item
     * @return bool
     */
    protected function validate($item)
    {
        // Valide le type
        if (!in_array($item[self::COLUMN_TYPE], $this->attributeTypesAvailable)) {
            $this->progress->addMessage('', $item[self::COLUMN_TYPE], 'attribute.type');
            return false;
        }

        return
            count($item) === 10
            && count(array_intersect_key($item, [
                "code_question" => true,
                "code_chapitre" => true,
                "texte_avant" => true,
                "libelle_question" => true,
                "format_reponse" => true,
                "items_reponse" => true,
                "colorer_reponse" => true,
                "infobulle_question" => true,
                "ponderation_categorie" => true,
                "ponderation_chapitre" => true,
            ])) === 10;
    }

    /**
     * Parse CSV float value
     *
     * @param $value
     * @return float
     */
    protected function parseFloatValue($value)
    {
        return floatval(str_replace(',', '.', $value));
    }
}
