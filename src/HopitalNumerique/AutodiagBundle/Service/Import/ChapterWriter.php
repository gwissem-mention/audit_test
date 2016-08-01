<?php
namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Nodevo\Component\Import\Writer\WriterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChapterWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    /** @var ValidatorInterface */
    protected $validator;

    protected $hasViolations = false;

    protected $importedChapterCodes = [];

    protected $mapping = [
        'titre_avant' => 'title',
        'texte_avant' => 'description',
        'texte_apres' => 'additionalDescription',
    ];

    public function __construct(EntityManager $manager, Autodiag $autodiag, ValidatorInterface $validator)
    {
        $this->manager = $manager;
        $this->autodiag = $autodiag;
        $this->validator = $validator;
    }

    public function prepare()
    {

    }

    public function write($item)
    {
        if ($this->validate($item)) {
            $parentChapter = null;

            if (isset($item['code_chapitre_enfant'])) {
                if (!isset($item['code_chapitre'])) {
                    $this->progress->addMessage(
                        'ad.import.chapter.missing_parent_code',
                        null,
                        'missing_parent_code',
                        'error'
                    );
                    return;
                }
                $parentCode = (string)$item['code_chapitre'];
                $chapterCode = (string)$item['code_chapitre_enfant'];

                $parentChapter = $this->getChapter($parentCode);
            } else {
                $chapterCode = (string)$item['code_chapitre'];
            }

            if (array_key_exists($chapterCode, $this->importedChapterCodes)) {
                $this->progress->addMessage(
                    '',
                    $chapterCode,
                    'chapter_exists'
                );
                return;
            }
            $this->importedChapterCodes[$chapterCode] = true;

            $chapter = $this->getChapter($chapterCode);
            $propertyAccessor = new PropertyAccessor();

            foreach ($this->mapping as $key => $attribute) {
                if (array_key_exists($key, $item)) {
                    $propertyAccessor->setValue($chapter, $attribute, (string)$item[$key]);
                }
            }

            if ($parentChapter) {
                $chapter->setParent($parentChapter);
                $chapter->setLabel($item['libelle_chapitre_enfant']);
            } else {
                $chapter->setParent();
                $chapter->setLabel($item['libelle_chapitre']);
            }

            $this->handleActionPlan($chapter, $item);

            $violations = $this->validator->validate($chapter);
            if (count($violations) > 0) {
                $this->progress->addMessage(
                    '',
                    $violations,
                    'violation',
                    'chapter'
                );
                $this->hasViolations = true;
                return;
            }

            $this->progress->addMessage('', $chapter, 'chapter_updated');
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
        if ($this->hasViolations) {
            $this->manager->clear();
            $this->progress->addError('ad.import.chapter.has_violations');
            return;
        }

        $toDelete = $this->autodiag->getChapters()->filter(
            function (Chapter $chapter) {
                return !array_key_exists((string)$chapter->getCode(), $this->importedChapterCodes);
            }
        );

        foreach ($toDelete as $chapter) {
            $this->progress->addMessage('', $chapter, 'chapter.delete');
            $this->autodiag->removeChapter($chapter);
        }

        $this->manager->persist($this->autodiag);
        $this->manager->flush();
    }

    protected function getChapter($code)
    {
        $chapter = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Container\Chapter')
            ->findOneBy([
                'autodiag' => $this->autodiag,
                'code' => $code
            ]);

        if (!$chapter instanceof Chapter) {
            $chapter = $this->autodiag->getChapters()->filter(function (Chapter $chapter) use ($code) {
                return $chapter->getCode() == $code;
            })->first();

            if (!$chapter) {
                $chapter = new Chapter();
                $chapter->setCode($code);
                $this->autodiag->addChapter($chapter);
            }
        }

        return $chapter;
    }

    protected function handleActionPlan(Chapter $chapter, $item)
    {
        if (strlen($item['plan_action']) === 0) {
            return;
        }

        $actions = preg_split("/\\r\\n|\\r|\\n/", $item['plan_action']);
        array_walk($actions, function (&$element) {
            $element = explode("::", $element);
        });

        $updatedActions = [];

        foreach ($actions as $action) {
            $value = $this->parseFloatValue($action[1]);
            $object = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\ActionPlan')
                ->findOneBy([
                    'container' => $chapter,
                    'value' => $value,
                ]);

            if (null === $object) {
                $object = Autodiag\ActionPlan::createForContainer($this->autodiag, $chapter, $value);
                $this->manager->persist($object);
            }

            $object->setVisible((bool)$action[1]);
            $object->setDescription(isset($action[2]) ? $action[2] : null);
            $object->setLink(isset($action[3]) ? $action[3] : null);
            $object->setLinkDescription(isset($action[4]) ? $action[4]: null);

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
            }
        }

        $actionPlans = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\ActionPlan')
            ->findBy([
                'container' => $chapter,
            ]);
        foreach ($actionPlans as $actionPlan) {
            if (!array_key_exists($actionPlan->getId(), $updatedActions)) {
                $this->manager->remove($actionPlan);
            }
        }

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

    protected function validate($item)
    {
        return
            count($item) === 8
            && count(array_intersect_key($item, [
                'code_chapitre' => true,
                'code_chapitre_enfant' => true,
                'libelle_chapitre' => true,
                'libelle_chapitre_enfant' => true,
                'titre_avant' => true,
                'texte_avant' => true,
                'texte_apres' => true,
                'plan_action' => true,
            ])) === 8;
    }
}
