<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\ConnaissanceAmbassadeur;
use HopitalNumerique\UserBundle\Entity\User;

class Person implements ItemInterface
{
    /**
     * @var User $person
     */
    protected $person;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * Person constructor.
     *
     * @param User $person
     * @param array $references
     */
    public function __construct(User $person, $references)
    {
        $this->person = $person;
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->person->getNomPrenom();
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->person->getPhoneNumber();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->person->getEmail();
    }

    /**
     * @return string
     */
    public function getCounty()
    {
        return $this->person->getRegion()->getLibelle();
    }

    /**
     * @return string
     */
    public function getSystem()
    {
        return $this->person->getNomEtablissement();
    }

    /**
     * @return string
     */
    public function getSystemJob()
    {
        return $this->person->getJobLabel();
    }

    /**
     * @return string
     */
    public function getBiography()
    {
        return html_entity_decode(strip_tags($this->person->getPresentation()), ENT_COMPAT|ENT_NOQUOTES, 'UTF-8');
    }

    public function getModules()
    {
        return '';
    }

    /**
     * @return Reference\Hobby[]|ArrayCollection
     */
    public function getHobbies()
    {
        return $this->person->getHobbies();
    }

    /**
     * @return ConnaissanceAmbassadeur
     */
    public function getSkills()
    {
        return $this->person->getConnaissancesAmbassadeurs();
    }

    /**
     * @return Reference[]|ArrayCollection
     */
    public function getComputerSkills()
    {
        return $this->person->getComputerSkills();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'person';
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}
