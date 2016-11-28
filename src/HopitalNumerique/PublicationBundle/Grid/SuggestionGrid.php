<?php

namespace HopitalNumerique\PublicationBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Objet.
 */
class SuggestionGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Objet.
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_publication.repository.suggestion');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('Aucune suggestion à afficher.');
    }

    public function setColumns()
    {
        $this->addColonne(new Column\TextColumn('title', 'Titre'));
        $this->addColonne(new Column\DateColumn('creationDate', 'Date'));
        $this->addColonne(
            (new Column\TextColumn('stateLabel', 'État'))
                ->setFilterType('select')
                ->setOperatorsVisible(false)
        );
        $this->addColonne(new Column\TextColumn('domainsName', 'Domaine(s)'));
    }

    public function setActionsButtons()
    {
        $this->addActionButton(new Action\EditButton('hopitalnumerique_suggestion_back_edit'));
    }

    public function setMassActions()
    {
    }
}
