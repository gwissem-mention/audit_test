<?php

namespace HopitalNumerique\AutodiagBundle\Model\Result;

class ComparedScore extends Score
{
    /** @var Score */
    protected $reference;

    /** @var float */
    protected $variation;

    /**
     * @return Score
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param Score $reference
     *
     * @return ComparedScore
     */
    public function setReference(Score $reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return float
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param float $variation
     *
     * @return ComparedScore
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;

        return $this;
    }
}
