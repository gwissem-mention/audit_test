<?php
namespace Nodevo\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ParamsType extends AbstractType
{
    private $_params = array();
    private $_route  = null;

    public function __construct( $route, $params )
    {
        $this->_params = json_decode( $params, true );
        $this->_route  = $route;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $pattern = '/\{([a-zA-Z]+)\}/';
        preg_match_all($pattern, $this->_route->getPattern(), $matches);

        if( isset($matches[1]) ){
            foreach( $matches[1] as $param ){
                $builder->add('routeParameters_'.$param, 'text', array(
                    'label'  => $param,
                    'mapped' => false,
                    'data'   => isset($this->_params[$param]) ? $this->_params[$param] : ''
                ));
            }
        }

        return $builder;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));
    }

    public function getName()
    {
        return 'nodevo_menu_item_params';
    }
}