<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ComparedItemResponse extends ItemResponse
{
    /** @var ItemResponse */
    protected $reference;

    /**
     * @return ItemResponse
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param ItemResponse $reference
     *
     * @return ComparedItemResponse
     */
    public function setReference(ItemResponse $reference)
    {
        $this->reference = $reference;

        return $this;
    }
}
