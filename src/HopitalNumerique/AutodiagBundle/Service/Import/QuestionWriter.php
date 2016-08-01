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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    const COLUMN_CODE = "code_question";
    const COLUMN_OPTIONS = "items_reponse";
    const COLUMN_CHAPTER = "code_chapitre";
    const COLUMN_CHAPTER_WEIGHT = "ponderation_chapitre";
    const COLUMN_CATEGORIES = "ponderation_categorie";
    const COLUMN_TYPE = "format_reponse";
    const COLUMN_COLORED = "colorer_reponse";


    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    /** @var AttributeBuilderProvider */
    protected $attributesProvider;

    /** @var array */
    protected $attributeTypesAvailable;

    /** @var ValidatorInterface */
    protected $validator;

    protected $chapters = [];

    protected $categories = [];

    protected $attributes = [];

    protected $weights = [];

    protected $mapping = [
        'texte_avant' => 'description',
        'libelle_question' => 'label',
        'format_reponse' => 'type',
        'colorer_reponse' => 'colored',
        'infobulle_question' => 'tooltip',
    ];

    public function __construct(EntityManager $manager, Autodiag $autodiag, AttributeBuilderProvider $attributesProvider, ValidatorInterface $validator)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
        $this->attributesProvider = $attributesProvider;
        $this->validator = $validator;

        $this->attributeTypesAvailable = $this->attributesProvider->getBuildersName();
    }

    public function prepare()
    {

    }

    public function write($item)
    {
        $this->weights = [];

        if ($this->validateRow($item)) {
            $attribute = $this->getAttribute($item[self::COLUMN_CODE], $item[self::COLUMN_TYPE]);

            if (isset($this->attributes[$attribute->getCode()])) {
                $this->progress->addMessage(
                    '',
                    $attribute->getCode(),
                    'attribute_exists'
                );
                return;
            }
            $this->attributes[$attribute->getCode()] = $attribute;

            $propertyAccessor = new PropertyAccessor();
            foreach ($this->mapping as $key => $property) {
                if (array_key_exists($key, $item) && null !== $item[$key]) {
                    $propertyAccessor->setValue($attribute, $property, (string)$item[$key]);
                }
            }

            $this->handleOptions($attribute, $item[self::COLUMN_OPTIONS]);
            $chapterValid = $this->handleChapter($attribute, $item[self::COLUMN_CHAPTER], $item[self::COLUMN_CHAPTER_WEIGHT]);
            $this->handleCategories($attribute, $item[self::COLUMN_CATEGORIES]);

            $this->manager->persist($attribute);

            $violations = $this->validator->validate($attribute);
            $hasViolations = count($violations) > 0;
            if (!$chapterValid || !$this->validate($item) || $hasViolations) {
                if ($hasViolations) {
                    $this->progress->addMessage(
                        '',
                        $violations,
                        'violation',
                        'attribute'
                    );
                }

                $this->manager->detach($attribute);
                foreach ($this->weights as $weight) {
                    $this->manager->detach($weight);
                }
                return;
            }

            $this->progress->addSuccess($item);
        } else {
            $this->progress->addError('ad.import.attribute.incorrect_row_format');
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
            if (!isset($this->attributes[$attribute->getCode()])) {
                $this->manager->remove($attribute);
            }
        }

        $categories = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Category')
            ->findBy([
                'autodiag' => $this->autodiag
            ]);
        foreach ($categories as $category) {
            if (!isset($this->categories[$category->getCode()])) {
                $this->manager->remove($category);
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
            return $option->getValue() === (float) $value;
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
            } elseif (strlen($line) > 0) {
                return false;
            }
        }

        return $options;
    }

    /**
     * Handle attribute options
     *
     * @param Attribute $attribute
     * @param $options
     * @return bool
     */
    protected function handleOptions(Attribute $attribute, $options)
    {
        $collection = new ArrayCollection();
        $optionsArray = $this->parseMultiline($options);
        if (false === $optionsArray) {
            $this->progress->addMessage(
                '',
                $options ?: '',
                'attribute_options'
            );
            return false;
        }
        foreach ($optionsArray as $value => $label) {
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
                $this->progress->addMessage(
                    '',
                    $element,
                    'option_removed'
                );
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
            $this->progress->addMessage('', $code ?: '', 'chapter_notfound');
            return false;
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
            $this->weights[] = $weightObject;
        }
        return true;
    }

    /**
     * Handle attribute categories and their weight
     *
     * @param Attribute $attribute
     * @param $categoriesData
     */
    protected function handleCategories(Attribute $attribute, $data)
    {
        $categoriesData = $this->parseMultiline($data);
        if (false === $categoriesData) {
            $this->progress->addMessage(
                '',
                $data ?: '',
                'attribute_category'
            );
            return false;
        }
        foreach ($categoriesData as $code => $weight) {
            $category = $this->getCategory($code);
            if (false === $category) {
                return false;
            }
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
            $this->weights[] = $weightObject;
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

                $violations = $this->validator->validate($category);
                if (count($violations) > 0) {
                    $this->manager->detach($category);

                    $this->progress->addMessage(
                        '',
                        $violations,
                        'violation',
                        'category'
                    );
                    return false;
                }

                $this->manager->persist($category);
                $this->progress->addMessage(
                    '',
                    $category,
                    'category_created'
                );
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
    protected function validateRow($item)
    {
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
    protected function validate($item)
    {
        // Valide le type
        if (isset($item[self::COLUMN_TYPE]) && !in_array($item[self::COLUMN_TYPE], $this->attributeTypesAvailable)) {
            $this->progress->addMessage('', $item, 'attribute_type');
            return false;
        }

        if (isset($item[self::COLUMN_COLORED]) && !in_array($item[self::COLUMN_COLORED], ["1", "-1"])) {
            $this->progress->addMessage('', $item[self::COLUMN_COLORED] ?: '', 'attribute_colored');
            return false;
        }

        return true;
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
