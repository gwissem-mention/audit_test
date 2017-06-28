<?php

namespace Nodevo\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParamsType extends AbstractType
{
    private $params = [];
    private $route = null;

    public function __construct($route, $params)
    {
        $this->params = json_decode($params, true);
        $this->route = $route;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->route) {
            $pattern = '/\{([a-zA-Z]+)\}/';
            preg_match_all($pattern, $this->route->getPattern(), $matches);

            if (isset($matches[1])) {
                foreach ($matches[1] as $param) {
                    $builder->add('routeParameters_' . $param, 'text', [
                        'label' => $param,
                        'mapped' => false,
                        'data' => isset($this->params[$param]) ? $this->params[$param] : '',
                    ]);
                }
            }
        }

        return $builder;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

    public function getName()
    {
        return 'nodevo_menu_item_params';
    }
}
