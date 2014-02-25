<?php
namespace HopitalNumerique\ObjetBundle\Twig;

class ObjetExtension extends \Twig_Extension
{
    /**
     * Construit l'extension Twig
     */
    public function __construct()
    {

    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'rearangeDatas' => new \Twig_Filter_Method($this, 'rearangeDatas')
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
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'objet_extension';
    }
}