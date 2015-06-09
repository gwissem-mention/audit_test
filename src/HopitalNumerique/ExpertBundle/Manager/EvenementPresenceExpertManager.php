<?php

namespace HopitalNumerique\ExpertBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

use HopitalNumerique\ExpertBundle\Entity\EvenementExpert;

/**
 * Manager de l'entité EvenementPresenceExpert.
 */
class EvenementPresenceExpertManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ExpertBundle\Entity\EvenementPresenceExpert';

    /**
     * [majExperts description]
     *
     * @param EvenementExpert $evenementExpert [description]
     *
     * @return [type]
     */
    public function majExperts(EvenementExpert $evenementExpert)
    {
        //Récupération des experts de l'activité
        $experts    = $evenementExpert->getActivite()->getExpertConcernes();
        $presences  = array();
        $expertsIds = array();

        foreach ($experts as $expert)
        {
            if(!in_array($expert->getId(), $evenementExpert->getExpertsIds()))
            {
                $presence = $this->createEmpty();
                $presence->setExpertConcerne($expert);
                $presence->setEvenement($evenementExpert);

                $presences[] = $presence;
            }

            $expertsIds[] = $expert->getId();
        }

        //Retire les experts qui ne sont plus dans l'activité
        foreach ($evenementExpert->getExperts() as $expertEvenement) 
        {
            if(!in_array($expertEvenement->getExpertConcerne()->getId(), $expertsIds))
            {
                $this->delete($expertEvenement);
            }
        }

        $this->save($presences);
    }

}
