<?php
namespace HopitalNumerique\AutodiagBundle\Service\Import;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use Nodevo\Component\Import\Progress\ProgressAwareInterface;
use Nodevo\Component\Import\Progress\ProgressAwareTrait;
use Nodevo\Component\Import\Writer\WriterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ChapterWriter implements WriterInterface, ProgressAwareInterface
{
    use ProgressAwareTrait;

    /** @var EntityManager */
    protected $manager;

    /** @var Autodiag */
    protected $autodiag;

    protected $importedChapterCodes = [];

    protected $mapping = [
        'code_chapitre' => 'code',
        'code_chapitre_enfant' => 'code',
        'libelle_chapitre' => 'label',
        'libelle_chapitre_enfant' => 'label',
        'titre_avant' => 'title',
        'texte_avant' => 'description',
        'texte_apres' => 'additionalDescription',
//        'plan_action' => '',
    ];

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
            $parentChapter = null;

            if (isset($item['code_chapitre_enfant'])) {
                if (!isset($item['code_chapitre'])) {
                    throw new \Exception('Parent code needed for child chapter');
                }
                $parentCode = $item['code_chapitre'];
                $chapterCode = (string)$item['code_chapitre_enfant'];

                $parentChapter = $this->getChapter($parentCode);
            } else {
                $chapterCode = (string)$item['code_chapitre'];
            }

            $chapter = $this->getChapter($chapterCode);
            $propertyAccessor = new PropertyAccessor();

            foreach ($this->mapping as $key => $attribute) {
                if (array_key_exists($key, $item) && null !== $item[$key]) {
                    $propertyAccessor->setValue($chapter, $attribute, (string)$item[$key]);
                }
            }

            $this->progress->addMessage('', $chapter, 'chapter.updated');

            if ($parentChapter) {
                $chapter->setParent($parentChapter);
            } else {
                $chapter->setParent();
            }
            
            $this->progress->addSuccess($item);
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
        $toDelete = $this->autodiag->getChapters()->filter(
            function (Chapter $chapter) {
                return !array_key_exists($chapter->getCode(), $this->importedChapterCodes);
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
        $chapter = $this->autodiag->getChapters()->filter(
            function (Chapter $chapter) use ($code) {
                return $chapter->getCode() == $code;
            }
        )->first();

        if (!$chapter instanceof Chapter) {
            $chapter = new Chapter();
            $chapter->setCode($code);
            $this->autodiag->addChapter($chapter);
        }

        $this->importedChapterCodes[$chapter->getCode()] = true;

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
