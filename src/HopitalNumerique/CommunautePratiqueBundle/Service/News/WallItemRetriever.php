<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\News;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\DTO\News\WallItemInterface;
use HopitalNumerique\CommunautePratiqueBundle\Service\News\Retriever\WallItemRetrieverInterface;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WallItemRetriever implements WallItemRetrieverInterface
{
    /**
     * @var WallItemRetrieverInterface[] $retrievers
     */
    protected $retrievers = [];

    /**
     * @var TokenStorageInterface $tokenStorageInterface
     */
    protected $tokenStorageInterface;

    /**
     * WallItemRetriever constructor.
     *
     * @param TokenStorageInterface $tokenStorageInterface
     */
    public function __construct(TokenStorageInterface $tokenStorageInterface)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    /**
     * @param WallItemRetrieverInterface $itemRetriever
     * @param string $alias
     */
    public function addRetriever(WallItemRetrieverInterface $itemRetriever, $alias)
    {
        $this->retrievers[$alias] = $itemRetriever;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return WallItemInterface[]
     */
    public function retrieve(Domaine $domain = null)
    {
        $user = $this->tokenStorageInterface->getToken()->getUser();

        $items = [];
        foreach ($this->retrievers as $retriever) {
            if (!$retriever->isPublic() && (!$user instanceof User || !$user->isInscritCommunautePratique())) {
                continue;
            }

            $items = array_merge($items, $retriever->retrieve($domain));
        }

        usort($items, function (WallItemInterface $a, WallItemInterface $b) {
            if ($a->getDate() === $b->getDate()) {
                return 0;
            }

            return $a->getDate() > $b->getDate() ? -1 : 1;
        });

        return array_slice($items, 0, 20);
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return true;
    }
}
