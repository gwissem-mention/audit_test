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
//        'code_chapitre' => 'code',
//        'code_chapitre_enfant' => 'code',
//        'libelle_chapitre' => 'label',
//        'libelle_chapitre_enfant' => 'label',
        'titre_avant' => 'title',
        'texte_avant' => 'description',
        'texte_apres' => 'additionalDescription',
//        'plan_action' => '',
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
            ])) === 7;
    }
}
