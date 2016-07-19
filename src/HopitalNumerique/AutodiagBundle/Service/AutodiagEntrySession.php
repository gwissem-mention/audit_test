<?php

namespace HopitalNumerique\AutodiagBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AutodiagEntrySession
{
    const SESSION_KEY = 'ad.entry.current';

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var SessionInterface
     */
    protected $session;

    protected $entries;

    public function __construct(EntityRepository $repository, SessionInterface $session)
    {
        $this->repository = $repository;
        $this->session = $session;
        $this->entries = new ArrayCollection();
        $this->initEntries();
    }

    public function add(AutodiagEntry $entry)
    {
        $this->entries->add($entry);
        $this->persist();
    }

    /**
     * @param Autodiag $autodiag
     * @return \Doctrine\Common\Collections\Collection
     */
    public function get(Autodiag $autodiag)
    {
        return $this->entries->filter(function (AutodiagEntry $entry) use ($autodiag) {
            return $entry->getSynthesis()->getAutodiag() === $autodiag;
        });
    }

    public function remove(AutodiagEntry $entry)
    {
        $this->entries->removeElement($entry);
        $this->persist();
    }

    public function has(AutodiagEntry $entry)
    {
        return $this->entries->contains($entry);
    }

    protected function initEntries()
    {
        $ids = $this->session->get(self::SESSION_KEY);
        if (count($ids) > 0) {
            $entries = $this->repository->findBy(
                [
                    'id' => $ids
                ]
            );
            foreach ($entries as $entry) {
                $this->entries->add($entry);
            }
        }
    }

    protected function persist()
    {
        $this->session->set(
            self::SESSION_KEY,
            $this->entries->map(function (AutodiagEntry $entry) {
                return $entry->getId();
            })->getValues()
        );
    }
}
