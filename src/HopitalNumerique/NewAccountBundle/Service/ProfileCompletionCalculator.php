<?php

namespace HopitalNumerique\NewAccountBundle\Service;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class ProfileCompletionCalculator
 */
class ProfileCompletionCalculator
{
    /**
     * Get the percent of completion
     *
     * @param User $user
     *
     * @return float|int
     */
    public function calculateForUser(User $user)
    {
        $propertyAccessor = new PropertyAccessor();
        $fieldsCount = $fieldsCompletedCount = 0;

        foreach ($this->getFields() as $tab) {
            foreach ($tab as $field) {
                list($fieldsCount, $fieldsCompletedCount) = $this->testField($fieldsCount, $fieldsCompletedCount, $user, $field);
            }
        }

        // Specific conditions for structure
        if (!empty($propertyAccessor->getValue($user, 'organization'))) {
            $structureFields = ['organization', 'activities'];
        } elseif (!empty($propertyAccessor->getValue($user, 'organizationLabel'))) {
            $structureFields = ['organizationType', 'organizationLabel', 'region', 'county'];
        } else {
            $structureFields = ['organization', 'activities', 'organizationType', 'organizationLabel', 'region', 'county'];
        }

        foreach ($structureFields as $field) {
            list($fieldsCount, $fieldsCompletedCount) = $this->testField($fieldsCount, $fieldsCompletedCount, $user, $field);
        }

        return round($fieldsCompletedCount / ($fieldsCount ?: 1) * 100);
    }

    /**
     * @param integer $fieldsCount
     * @param integer $fieldsCompletedCount
     * @param User $user
     * @param string $field
     *
     * @return array
     */
    private function testField($fieldsCount, $fieldsCompletedCount, User $user, $field)
    {
        $propertyAccessor = new PropertyAccessor();
        $fieldsCount++;

        if ($propertyAccessor->isReadable($user, $field)) {
            if (!empty($propertyAccessor->getValue($user, $field))) {
                $fieldsCompletedCount++;
            }
        } else {
            throw new \LogicException($field);
        }

        return [
            $fieldsCount,
            $fieldsCompletedCount,
        ];
    }

    /**
     * Get the first incomplete tab in profile page
     *
     * @param User $user
     *
     * @return string
     */
    public function getFirstTabToCompleteForUser(User $user)
    {
        $propertyAccessor = new PropertyAccessor();
        foreach ($this->getFields() as $tabId => $tab) {
            foreach ($tab as $field) {
                if ($propertyAccessor->isReadable($user, $field)) {
                    if (empty($propertyAccessor->getValue($user, $field))) {
                        return $tabId;
                    }
                } else {
                    throw new \LogicException($field);
                }
            }
        }

        return 'structure';
    }

    /**
     * Get required fields to check for completion rate calcul
     *
     * @return array
     */
    private function getFields()
    {
        return [
            'personal_information' => [
                'firstname',
                'lastname',
                'email',
                'pseudonym',
            ],
            'contact_information' => [
                'phoneNumber',
                'cellPhoneNumber',
                'otherContact',
            ],
            'profile' => [
                'profileType',
                'jobType',
                'jobLabel',
            ],
            'skills' => [
                'presentation',
            ],
        ];
    }
}
