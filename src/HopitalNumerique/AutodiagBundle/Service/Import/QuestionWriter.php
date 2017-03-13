<?php

namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Model\FileImport\AttributeColumnsDefinition;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Nodevo\Component\Import\Writer\WriterInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

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
    protected $actionPlans = [];

    protected $mapping = [
        AttributeColumnsDefinition::DESCRIPTION => 'description',
        AttributeColumnsDefinition::LABEL => 'label',
        AttributeColumnsDefinition::TYPE => 'type',
        AttributeColumnsDefinition::TOOLTIP => 'tooltip',
        AttributeColumnsDefinition::NUMBER => 'number',
    ];

    /** @var Translator $translator */
    protected $translator;

    public function __construct(EntityManager $manager, Autodiag $autodiag, AttributeBuilderProvider $attributesProvider, ValidatorInterface $validator, Translator $translator)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
        $this->attributesProvider = $attributesProvider;
        $this->validator = $validator;
        $this->translator = $translator;

        $this->attributeTypesAvailable = $this->attributesProvider->getBuildersName();
    }

    public function prepare()
    {
    }

    public function write($item)
    {
        $this->weights = [];
        $this->actionPlans = [];

        if ($this->validateRow($item)) {
            $attribute = $this->getAttribute($item[AttributeColumnsDefinition::CODE]);

            if (isset($this->attributes[$attribute->getCode()])) {
                $this->progress->addMessage(
                    '',
                    $attribute->getCode() ?: '',
                    'attribute_exists'
                );

                return;
            }

            $this->attributes[$attribute->getCode()] = $attribute;

            $propertyAccessor = new PropertyAccessor();
            foreach ($this->mapping as $key => $property) {
                if (array_key_exists($key, $item)) {
                    $propertyAccessor->setValue($attribute, $property, (string) $item[$key]);
                }
            }

            if ($item[AttributeColumnsDefinition::COLORED] == '1') {
                $attribute->setColored(true);
                $attribute->setColorationInversed(false);
            } elseif ($item[AttributeColumnsDefinition::COLORED] == '-1') {
                $attribute->setColored(true);
                $attribute->setColorationInversed(true);
            } else {
                $attribute->setColored(false);
            }

            $attribute->setOrder($item[AttributeColumnsDefinition::ORDER]);

            $this->handleOptions($attribute, $item[AttributeColumnsDefinition::OPTIONS]);
            $chapterValid = $this->handleChapter($attribute, $item[AttributeColumnsDefinition::CHAPTER], $item[AttributeColumnsDefinition::CHAPTER_WEIGHT]);
            $this->handleCategories($attribute, $item[AttributeColumnsDefinition::CATEGORY_WEIGHT]);

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

                foreach ($this->actionPlans as $action) {
                    $this->manager->detach($action);
                }

                return;
            }

            $weights = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight')
                ->findBy([
                    'attribute' => $attribute,
                ]);
            foreach ($weights as $weight) {
                if (!in_array($weight, $this->weights)) {
                    $this->manager->remove($weight);
                }
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
                'autodiag' => $this->autodiag,
            ]);
        foreach ($categories as $category) {
            if (!isset($this->categories[$category->getCode()])) {
                $this->manager->remove($category);
            }
        }

        $this->manager->flush();
    }

    /**
     * Get an existing attribute or create a new one.
     *
     * @param $code
     *
     * @return Attribute
     */
    protected function getAttribute($code)
    {
        $attribute = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute')->findOneBy([
            'autodiag' => $this->autodiag,
            'code' => $code,
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
     * Get an existing Option for $attribute or create a new one and append it to $attribute.
     *
     * @param Attribute $attribute
     * @param $value
     *
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
     * Parse multiline cells (key::value\n...).
     *
     * @param $input
     *
     * @return array
     */
    protected function parseMultiline($input)
    {
        $lines = preg_split('/\\r\\n|\\r|\\n/', $input);
        $options = [];

        foreach ($lines as $line) {
            if (strlen($line) > 0) {
                $options[] = explode('::', $line);
            }
        }

        return $options;
    }

    /**
     * Handle attribute options.
     *
     * @param Attribute $attribute
     * @param           $options
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function handleOptions(Attribute $attribute, $options)
    {
        $collection = new ArrayCollection();
        $optionsArray = $this->parseMultiline($options);

        $unique = count(array_unique(array_map(function ($element) {
            return $element[0];
        }, $optionsArray))) === count($optionsArray);

        if (!$unique) {
            throw new \Exception($this->translator->trans('ad.import.attribute.incorrect_items_reponse', ['%code%' => $attribute->getCode()]));
        }

        foreach ($optionsArray as $data) {
            if (count($data) < 2) {
                $this->progress->addMessage(
                    '',
                    $options ?: '',
                    'attribute_options'
                );

                return false;
            }

            $value = $data[0];
            $label = $data[1];
            $option = $this->getOption($attribute, $value);
            $option->setLabel($label);
            $collection->add($option);

            $this->handleActionPlan($attribute, $value, $data);
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

    protected function handleActionPlan(Attribute $attribute, $value, $data)
    {
        $object = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\ActionPlan')
            ->findOneBy([
                'attribute' => $attribute,
                'value' => $value,
            ]);

        if (count($data) <= 2 && null !== $object) {
            $this->manager->remove($object);
            $this->actionPlans[] = $object;

            return true;
        } elseif (count($data) > 2) {
            if (count($data) !== 6) {
                $this->progress->addMessage('', implode(' - ', $data), 'attribute_actionplan_invalid');

                return false;
            }

            if (null === $object) {
                $object = Autodiag\ActionPlan::createForAttribute($this->autodiag, $attribute, $value);
                $this->manager->persist($object);
            }

            $object->setVisible((bool) $data[2]);
            $object->setDescription(isset($data[3]) ? $data[3] : null);
            $object->setLink(isset($data[5]) ? $data[5] : null);
            $object->setLinkDescription(isset($data[4]) ? $data[4] : null);

            $updatedActions[$object->getId()] = true;

            $violations = $this->validator->validate($object);

            if (count($violations) > 0) {
                $this->progress->addMessage(
                    '',
                    $violations,
                    'violation',
                    'actionplan'
                );
                $this->manager->detach($object);
            } else {
                $this->actionPlans[] = $object;
            }
        }

        return true;
    }

    /**
     * Handle attribute chapter and weight.
     *
     * @param Attribute $attribute
     * @param $code
     * @param $weight
     *
     * @return bool
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
                    'container' => $chapter,
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
     * Handle attribute categories and their weight.
     *
     * @param Attribute $attribute
     * @param $data
     *
     * @return bool
     */
    protected function handleCategories(Attribute $attribute, $data)
    {
        $categoriesData = $this->parseMultiline($data);

        foreach ($categoriesData as $categoryData) {
            if (count($categoryData) !== 2) {
                $this->progress->addMessage(
                    '',
                    $data ?: '',
                    'attribute_category'
                );

                return false;
            }

            $code = $categoryData[0];
            $weight = $categoryData[1];

            $category = $this->getCategory($code);
            if (false === $category) {
                return false;
            }
            $weightObject = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight')
                ->findOneBy([
                    'attribute' => $attribute,
                    'container' => $category,
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
     * Get existing chapter.
     *
     * @param $code
     *
     * @return mixed
     */
    protected function getChapter($code)
    {
        if (!array_key_exists((string) $code, $this->chapters)) {
            $chapter = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Chapter')
                ->findOneBy([
                    'autodiag' => $this->autodiag,
                    'code' => $code,
                ]);

            $this->chapters[$code] = $chapter;
        }

        return $this->chapters[$code];
    }

    /**
     * Get existing category.
     *
     * @param $code
     *
     * @return mixed
     */
    protected function getCategory($code)
    {
        if (!array_key_exists((string) $code, $this->categories)) {
            $category = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Category')
                ->findOneBy([
                    'autodiag' => $this->autodiag,
                    'code' => $code,
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
     * Validate item line.
     *
     * @param $item
     *
     * @return bool
     */
    protected function validateRow($item)
    {
        return
            count($item) === count(AttributeColumnsDefinition::getColumns())
            && count(
                array_intersect_key(array_keys($item), AttributeColumnsDefinition::getColumns())
            ) === count(AttributeColumnsDefinition::getColumns());
    }

    protected function validate($item)
    {
        // Valide le type
        if (isset($item[AttributeColumnsDefinition::TYPE]) && !in_array($item[AttributeColumnsDefinition::TYPE], $this->attributeTypesAvailable)) {
            $this->progress->addMessage('', $item, 'attribute_type');

            return false;
        }

        if (isset($item[AttributeColumnsDefinition::COLORED]) && !in_array($item[AttributeColumnsDefinition::COLORED], ['1', '-1', '0'])) {
            $this->progress->addMessage('', $item[AttributeColumnsDefinition::COLORED] ?: '', 'attribute_colored');

            return false;
        }

        return true;
    }

    /**
     * Parse CSV float value.
     *
     * @param $value
     *
     * @return float
     */
    protected function parseFloatValue($value)
    {
        return floatval(str_replace(',', '.', $value));
    }
}
