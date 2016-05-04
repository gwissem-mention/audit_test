<?php
namespace HopitalNumerique\ObjetBundle\Twig;

use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

class ObjetExtension extends \Twig_Extension
{
    private $_refManager;

    /**
     * Construit l'extension Twig
     */
    public function __construct( ReferenceManager $refManager )
    {
        $this->_refManager = $refManager;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'rearangeDatas'       		=> new \Twig_Filter_Method($this, 'rearangeDatas'),
        	'rearangeDatasAmbassadeur'  => new \Twig_Filter_Method($this, 'rearangeDatasAmbassadeur'),
        	'rearangeDatasExpert'       => new \Twig_Filter_Method($this, 'rearangeDatasExpert'),	
            'formateHistoryValue' 		=> new \Twig_Filter_Method($this, 'formateHistoryValue')
        );
    }

    /**
     * Retourne le tableau réarangé en chaine de caractère
     *
     * @param array $datas Tableau de datas
     *
     * @return string
     */
    public function rearangeDatas( $datas, $field )
    {
        $field    = 'get'. ucfirst($field);
        $newDatas = array();
        foreach($datas as $data)
            $newDatas[] = call_user_func( array( $data, $field ) );

        return implode(', ', $newDatas);
    }

    
    /**
     * Retourne le tableau réarangé en chaine de caractère trié par role Expert
     *
     * @param array $datas Tableau de datas
     *
     * @return string
     */
    public function rearangeDatasAmbassadeur( $datas, $field )
    {
    	$field    = 'get'. ucfirst($field);
    	$newDatas = array();
    	foreach($datas as $data)
    		if ($data->isGranted('ROLE_AMBASSADEUR_7')){
    			$newDatas[] = call_user_func( array( $data, $field ) );    			
    		}
    
    		return implode(', ', $newDatas);
    }
    
    /**
     * Retourne le tableau réarangé en chaine de caractère trié par role Expert
     *
     * @param array $datas Tableau de datas
     *
     * @return string
     */
    public function rearangeDatasExpert( $datas, $field )
    {
    	$field    = 'get'. ucfirst($field);
    	$newDatas = array();
    	foreach($datas as $data)
        	if ($data->isGranted('ROLE_EXPERT_6')){
    			$newDatas[] = call_user_func( array( $data, $field ) );    			
    		}
    
    		return implode(', ', $newDatas);
    }
    /**
     * Retourne la donnée d'historique formatée correctement
     *
     * @param array $datas La donnée
     *
     * @return string
     */
    public function formateHistoryValue( $data )
    {
        $return = '';

        if( is_array($data) ) {
            //Ref handle
            if( isset($data['id']) ){
                $ref    = $this->_refManager->findOneBy( array('id' => $data['id']) );
                $return = $ref->getLibelle();
            }else
                $return = implode('; ', $data);
        }else if( $data instanceof \DateTime ){
            $return = $data->format('d/m/Y');
        }else if( is_null($data) ){
            $return = 'NULL';
        }else
            $return = $data;

        return $return;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'objet_extension';
    }
}