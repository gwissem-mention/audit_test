<?php

namespace HopitalNumerique\EtablissementBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Class EtablissementManager
 */
class EtablissementManager extends BaseManager
{
    protected $class = '\HopitalNumerique\EtablissementBundle\Entity\Etablissement';

    /**
     * Retourne une liste d'établissements regroupée par type d'organisme.
     *
     * @param array $criteres Le filtre applicable
     *
     * @return array La liste des établissements trouvés regroupée par type d'organisme
     */
    public function getEtablissementsRegroupesParTypeOrganisme(array $criteres = null)
    {
        $etablissementsRegroupesParTypeOrganisme = [];
        $etablissements = $this->getRepository()->findBy($criteres, ['typeOrganisme' => 'ASC', 'nom' => 'ASC']);

        $typeOrganismeId = -1;
        foreach ($etablissements as $etablissement) {
            if ($etablissement->getTypeOrganisme() == null) {
                if ($typeOrganismeId == -1) {
                    $typeOrganismeId = null;

                    $etablissementsRegroupesParTypeOrganisme[] = [
                        'typeOrganisme' => null,
                        'departements' => [],
                    ];
                }
            } elseif ($etablissement->getTypeOrganisme()->getId() != $typeOrganismeId) {
                $typeOrganismeId = $etablissement->getTypeOrganisme()->getId();

                $etablissementsRegroupesParTypeOrganisme[] = [
                    'typeOrganisme' => $etablissement->getTypeOrganisme(),
                    'departements' => [],
                ];
            }

            $etablissementsRegroupesParTypeOrganisme[count($etablissementsRegroupesParTypeOrganisme) - 1]['etablissements'][] = $etablissement;
        }

        return $etablissementsRegroupesParTypeOrganisme;
    }

    /**
     * Récupère les données pour l'export CSV.
     *
     * @return array
     */
    public function getDatasForExport($ids)
    {
        return $this->getRepository()->getDatasForExport($ids)->getQuery()->getResult();
    }
}
