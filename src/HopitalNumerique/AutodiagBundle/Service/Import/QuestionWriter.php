<?php
namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Model;
use HopitalNumerique\AutodiagBundle\Entity\Model\Attribute;
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

    /** @var Model */
    protected $model;

    /** @var AttributeBuilderProvider */
    protected $attributesProvider;

    protected $attributeTypesAvailable;

    protected $chapters = [];

    protected $mapping = [
        'texte_avant' => 'description',
        'libelle_question' => 'label',
        'format_reponse' => 'type',
        'colorer_reponse' => 'colored',
        'infobulle_question' => 'tooltip',
    ];

    public function __construct(EntityManager $manager, Model $model, AttributeBuilderProvider $attributesProvider)
    {
        $this->manager = $manager;
        $this->model = $model;
        $this->attributesProvider = $attributesProvider;

        $this->attributeTypesAvailable = $this->attributesProvider->getBuildersName();
    }

    public function prepare()
    {

    }

    public function write($item)
    {
        if ($this->validate($item)) {

            $attribute = $this->getAttribute($item[self::COLUMN_CODE]);

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

        } else {
            $this->progress->addException(
                new \Exception('chapter incorect format')
            );
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
     * Get an existing attribute or create a new one
     *
     * @param $code
     * @return Attribute
     */
    protected function getAttribute($code)
    {
        $attribute = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Model\Attribute')->findOneBy([
            'model' => $this->model,
            'code' => $code
        ]);

        if (null === $attribute) {
            $attribute = new Attribute();
            $attribute
                ->setModel($this->model)
                ->setCode($code);
        }

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
            return $option->getValue() == $value;
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
                $attribute->removeOption($element);
            }
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
            $chapter = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Model\Container\Chapter')
                ->findOneBy([
                    'model' => $this->model,
                    'code' => $code
                ]);

            $this->chapters[$code] = $chapter;
        }

        return $this->chapters[$code];
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
            $weightObject = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Model\Attribute\Weight')
                ->findOneBy([
                    'attribute' => $attribute,
                    'container' => $chapter
                ]);

            if (null == $weightObject) {
                $weightObject = new Attribute\Weight($chapter, $attribute);
                $this->manager->persist($weightObject);
            }

            $weightObject->setWeight(floatval(str_replace(',', '.', $weight)));
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
            $category = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Model\Container\Category')
                ->findOneBy([
                    'model' => $this->model,
                    'code' => $code
                ]);
            if (null === $category) {
                $category = new Model\Container\Category();
                $category
                    ->setModel($this->model)
                    ->setCode($code)
                    ->setLabel($code);
                $this->manager->persist($category);
            }

            $weightObject = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Model\Attribute\Weight')
                ->findOneBy([
                    'attribute' => $attribute,
                    'container' => $category
                ]);

            if (null == $weightObject) {
                $weightObject = new Attribute\Weight($category, $attribute);
                $this->manager->persist($weightObject);
            }

            $weightObject->setWeight(floatval(str_replace(',', '.', $weight)));
        }
    }

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
}
