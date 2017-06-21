<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Elastica\Document;
use Elastica\Type;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\ContainerRepository;
use HopitalNumerique\SearchBundle\Service\IndexManager;

/**
 * Autodiag type provider
 *
 */
class AutodiagProvider extends AbstractProvider
{
    /**
     * @var \FOS\ElasticaBundle\Elastica\Index
     */
    protected $index;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var ContainerRepository
     */
    protected $containerRepository;

    /**
     * AutodiagProvider constructor.
     * @param $domaineSlug
     * @param EntityRepository $repository
     * @param ObjectPersisterInterface $persister
     * @param IndexManager $indexManager
     */
    public function __construct(
        $domaineSlug,
        EntityRepository $repository,
        ObjectPersisterInterface $persister,
        IndexManager $indexManager,
        ContainerRepository $containerRepository
    ) {
        parent::__construct($domaineSlug, $repository, $persister);
        $this->index = $indexManager->getIndexByDomaine($domaineSlug);
        $this->type = $this->index->getType('autodiag');
        $this->containerRepository = $containerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $chapterCodes = $this->computeChapterCodes();

        $total = $this->countObjects();
        foreach ($this->getData() as $attributes) {
            foreach ($attributes as $attribute) {
                $document = new Document(
                    $attribute['id'],
                    [
                        'title' => $attribute['label'],
                        'chapter_id' => $attribute['chapter_id'],
                        'chapter_label' => $attribute['chapter_label'],
                        'chapter_code' => $chapterCodes[$attribute['chapter_id']],
                        'autodiag_id' => $attribute['autodiag_id'],
                    ]
                );
                $this->type->addDocument($document);
            }
            $loggerClosure(count($attributes), $total);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $qb = $this->repository->createQueryBuilder('attribute');
        $qb
            ->select(
                'attribute.id',
                'attribute.label',
                'container.id as chapter_id',
                'container.label as chapter_label',
                'container.code as chapter_code',
                'autodiag.id as autodiag_id'
            )
            ->join('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute\Weight', 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container', Join::WITH, $qb->expr()->isInstanceOf('container', Chapter::class))
            ->join('container.autodiag', 'autodiag')
            ->join('autodiag.domaines', 'domaine', Join::WITH, 'domaine.slug = :domaineSlug')
            ->groupBy('attribute.id')
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    protected function countObjects()
    {
        $queryBuilder = $this->createQueryBuidler();
        return $queryBuilder
            ->select($queryBuilder->expr()->count('attribute'))
            ->resetDQLPart('orderBy')
            ->resetDQLPart('groupBy')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Get an array indexed by chapter id with recursive chapter code
     *
     * @return array
     */
    private function computeChapterCodes()
    {
        $codes = [];
        $chapters = $this->containerRepository->getChapters();

        foreach ($chapters as $chapter) {
            $code = $chapter->getCode();
            $parent = $chapter->getParent();
            while (null !== $parent) {
                $code = $parent->getCode() . '. ' . $code;
                $parent = $parent->getParent();
            }

            $codes[$chapter->getId()] = $code;
        }

        return $codes;
    }
}
