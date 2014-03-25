<?php

namespace Nodevo\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\EntityRepository;

class ItemType extends AbstractType
{
    private $_constraints = array();
    private $_routes      = array();
    private $_allRoutes   = array();

    public function __construct( $manager, $validator, $router )
    {
        $this->_constraints = $manager->getConstraints( $validator );
        $this->_allRoutes   = $router->getRouteCollection()->all();

        foreach( $this->_allRoutes as $key => $one ) {
            if ( $key[0] != '_' ) {
                $splitetKey = explode("_",$key);
                $groupKey   = (count($splitetKey) >= 2) ? $splitetKey[0]."_".$splitetKey[1] : $key;
                
                $this->_routes[$groupKey][$key] = $key . ' - ' . $one->getPath();
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];

        $builder
            ->add('name', 'text', array(
                'max_length' => $this->_constraints['name']['maxlength'],
                'required'   => true, 
                'label'      => 'Nom',
                'attr'       => array('class' => $this->_constraints['name']['class'] )
            ))
            
            ->add('route', 'choice', array(
                'choices'     => $this->_routes,
                'multiple'    => false,
                'empty_value' => ' - ',
                'label'       => 'Route',
                'required'    => false
            ));

        //Handle Route Parameters
        $route = isset($this->_allRoutes[$datas->getRoute()]) ? $this->_allRoutes[$datas->getRoute()] : null;
        $builder->add('routeParameters', new ParamsType( $route, $datas->getRouteParameters()), array(
            'required'   => false, 
            'label'      => 'Paramètres de la route sélectionnée',
            'mapped'     => false,
            'data_class' => null
        ));

        $builder
            ->add('uri', 'text', array(
                'max_length' => $this->_constraints['uri']['maxlength'],
                'required'   => false, 
                'label'      => 'URI'
            ))

            ->add('display', 'checkbox', array(
                'required' => false,
                'label'    => 'Afficher le lien',
                'attr'     => array( 'class'=> 'checkbox' )
            ))

            ->add('displayChildren', 'checkbox', array(
                'required' => false,
                'label'    => 'Elément parent ?',
                'attr'     => array( 'class'=> 'checkbox' )
            ))
            
            ->add('parent', 'entity', array(
                'class'       => 'NodevoMenuBundle:Item',
                'empty_value' => ' - ',
                'required'    => false,
                'label'       => 'Parent du lien',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('item')
                              ->andWhere('item.display = 1')
                              ->orderBy('item.order');
                }
            ))
            
            ->add('menu', 'entity', array(
                'class'       => 'NodevoMenuBundle:Menu',
                'label'       => 'Menu Associé',
                'empty_value' => ' - ',
                'required'    => true
            ))
            
            ->add('order', 'integer', array(
                'label' => 'Ordre d\'affichage',
                'attr'  => array('class' => $this->_constraints['order']['class'] )
            ))
            
            ->add('selectIcon', 'choice', array(
                    'label'      => 'Type d\'icône',
                    'required'   => false,
                    'mapped'     => false,
                    'empty_value'=> ' - ',
                    'choices'    => array(
                        'fa'   => 'Fontawesome',
                        'glyphicon' => 'Glyphicon'
                    )
            ))
            
            ->add('buttonIconGlyph', 'button', array(
                    'label'     => 'Icône',
                    'attr'      => array(
                        'class'          => 'btn btn-success iconpicker',
                        'role'           => 'iconpicker',
                        'data-placement' => 'right',
                        'data-rows'      => 3,
                        'data-cols'      => 6,
                        'data-iconset' => 'glyphicon'
                    )
            ))
            ->add('buttonIconFontAwesome', 'button', array(
                    'label'  => 'Icône',
                    'attr' => array(
                        'class' => 'btn btn-success iconpicker',
                        'role' => 'iconpicker',
                        'data-placement' => 'right',
                        'data-rows' => 3,
                        'data-cols' => 6,
                        'data-iconset' => 'fontawesome'
                    )
            ))
            
            ->add('icon', 'hidden');
    }

    public function getName()
    {
        return 'nodevo_menu_item';
    }
}