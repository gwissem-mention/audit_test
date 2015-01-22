<?php

namespace HopitalNumerique\EtablissementBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

class EtablissementManager extends BaseManager
{
    protected $_class = '\HopitalNumerique\EtablissementBundle\Entity\Etablissement';

    /**
     * Retourne une liste d'établissements regroupée par type d'organisme.
     * 
     * @param array $criteres Le filtre applicable
     * @return array La liste des établissements trouvés regroupée par type d'organisme
     */
    public function getEtablissementsRegroupesParTypeOrganisme(array $criteres = null)
    {
        $etablissementsRegroupesParTypeOrganisme = array();
        $etablissements = $this->getRepository()->findBy($criteres, array('typeOrganisme' => 'ASC', 'nom' => 'ASC'));
        
        $typeOrganismeId = -1;
        foreach ($etablissements as $etablissement)
        {
            if ($etablissement->getTypeOrganisme() == null)
            {
                if ($typeOrganismeId == -1)
                {
                    $typeOrganismeId = null;
                    
                    $etablissementsRegroupesParTypeOrganisme[] = array(
                        'typeOrganisme' => null,
                        'departements' => array()
                    );
                }
            }
            else if ($etablissement->getTypeOrganisme()->getId() != $typeOrganismeId)
            {
                $typeOrganismeId = $etablissement->getTypeOrganisme()->getId();
                
                $etablissementsRegroupesParTypeOrganisme[] = array(
                    'typeOrganisme' => $etablissement->getTypeOrganisme(),
                    'departements' => array()
                );
            }
            
            $etablissementsRegroupesParTypeOrganisme[count($etablissementsRegroupesParTypeOrganisme) - 1]['etablissements'][] = $etablissement;
        }
        
        return $etablissementsRegroupesParTypeOrganisme;
    }

    /**
     * Récupère les données pour l'export CSV
     *
     * @return array
     */
    public function getDatasForExport( $ids )
    {
        return $this->getRepository()->getDatasForExport( $ids )->getQuery()->getResult();
    }

    // public function delete( $etablissements )
    // {
    //     try 
    //     {
    //         parent::delete($etablissements);
    //         $this->_session->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
    //     }
    //     catch (\Exception $e)
    //     {
    //         $this->_session->getFlashBag()->add('danger', 'Cet établissement semble être lié à un ou plusieurs utilisateur(s). Vous ne pouvez pas le supprimer.' );
    //     }
    // }









}