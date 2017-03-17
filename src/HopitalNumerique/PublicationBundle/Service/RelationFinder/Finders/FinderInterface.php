<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders;

interface FinderInterface
{
    public function support($data);

    public function find($data);
}
