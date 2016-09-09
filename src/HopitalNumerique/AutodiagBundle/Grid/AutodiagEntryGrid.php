<?php
namespace HopitalNumerique\AutodiagBundle\Grid;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\GridBundle\Grid\Action\ActionMass;
use Nodevo\GridBundle\Grid\Action\EditButton;
use Nodevo\GridBundle\Grid\Column\BooleanColumn;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Column\NumberColumn;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;

class AutodiagEntryGrid extends Grid implements GridInterface
{
    protected $autodiag;

    public function setAutodiag(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;
    }

    public function setConfig()
    {
        $this->setSource('autodiag.entries.grid');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);

        $this->setSourceCondition('autodiag', $this->autodiag);
    }

    public function setColumns()
    {
        $this->addColonne(
            (new NumberColumn('id', 'ID'))->setVisible(false)
        );

        $this->addColonne(
            new TextColumn('name', 'Nom')
        );

        $this->addColonne(
            new TextColumn('user', 'Utilisateur')
        );

        $this->addColonne(
            new TextColumn('etablissement', 'Établissement')
        );

        $this->addColonne(
            new TextColumn('remplissage', 'Remplissage')
        );

        $this->addColonne(
            new DateColumn('created_at', 'Création')
        );

        $this->addColonne(
            new DateColumn('updated_at', 'Dernier enregistrement')
        );

        $this->addColonne(
            new DateColumn('validated_at', 'Validation')
        );

        $this->addColonne(
            new TextColumn('shares', 'Partagé avec')
        );

        $this->addColonne(
            new BooleanColumn('is_synthesis', 'Synthèse')
        );
    }

    public function setActionsButtons()
    {
        $this->addActionButton(
            new EditButton('hopitalnumerique_autodiag_edit')
        );
    }

    public function setMassActions()
    {
        $this->addMassAction(
            new ActionMass('Supprimer', function ($ids, $all) {
                //get all selected Codes
                if ($all == 1) {
                    $rawDatas = $this->getRawData();
                    foreach ($rawDatas as $data) {
                        $ids[] = $data['id'];
                    }
                }

                $autodiags = $this->_container->get('autodiag.repository.autodiag')->findBy([
                    'id' => $ids
                ]);

                try {
                    foreach ($autodiags as $autodiag) {
                        $this->_container->get('doctrine.orm.entity_manager')->remove($autodiag);
                    }
                    $this->_container->get('doctrine.orm.entity_manager')->flush();
                    $this->_container->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
                } catch (\Exception $e) {
                    $this->_container->get('session')->getFlashBag()->add('error', "Une erreur est survenue.");
                }
            })
        );
    }

    public function getDatasForGrid($autodiag)
    {
        $qb = $this->_container->get('autodiag.repository.synthesis')->getDatasForGrid($autodiag->value);
        $resultSet = $qb->getQuery()->getResult();

        $data = [];
        foreach ($resultSet as $synthesis) {
            /** @var Synthesis $synthesis */

            $shares = [];
            if ($synthesis->getShares()->count() > 0) {
                $shares = $synthesis->getShares()->map(function (User $user) {
                    return sprintf('%s %s', $user->getPrenom(), $user->getNom());
                })->toArray();
            }

            $data[] = [
                'id' => $synthesis->getId(),
                'name' => $synthesis->getName(),
                'user' => $synthesis->getUser() !== null
                    ? sprintf('%s %s', $synthesis->getUser()->getPrenom(), $synthesis->getUser()->getNom())
                    : 'Anonyme',
                'etablissement' => null !== $synthesis->getUser() && null !== $synthesis->getUser()->getEtablissementRattachementSante()
                    ? $synthesis->getUser()->getEtablissementRattachementSante()->getNom()
                    : '',
                'remplissage' => sprintf('%d%%', $this->_container->get('autodiag.synthesis.completion')->getCompletionRate($synthesis)),
                'created_at' => '',
                'updated_at' => $synthesis->getUpdatedAt(),
                'validated_at' => $synthesis->getValidatedAt(),
                'shares' => implode(", ", $shares),
                'is_synthesis' => $synthesis->getEntries()->count() > 1,
            ];
        }

        return $data;
    }
}
