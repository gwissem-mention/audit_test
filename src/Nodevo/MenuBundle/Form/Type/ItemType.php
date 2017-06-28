<?php

namespace Nodevo\MenuBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class ItemType extends AbstractType
{
    private $_constraints = [];
    private $_routes = [];
    private $_allRoutes = [];

    public function __construct($manager, $validator, $router)
    {
        $this->_constraints = $manager->getConstraints($validator);
        $this->_allRoutes = $router->getRouteCollection()->all();

        foreach ($this->_allRoutes as $key => $one) {
            if ($key[0] != '_') {
                $splitetKey = explode('_', $key);
                $groupKey = (count($splitetKey) >= 2) ? $splitetKey[0] . '_' . $splitetKey[1] : $key;

                $this->_routes[$groupKey][$key] = $key . ' - ' . $one->getPath();
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas = $options['data'];
        $menuId = $datas->getMenu()->getId();

        $builder
            ->add('name', 'text', [
                'max_length' => $this->_constraints['name']['maxlength'],
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => $this->_constraints['name']['class']],
            ])

            ->add('route', 'choice', [
                'choices' => $this->_routes,
                'multiple' => false,
                'empty_value' => ' - ',
                'label' => 'Route',
                'required' => false,
            ])
            ->add('uri', 'text', [
                'max_length' => $this->_constraints['uri']['maxlength'],
                'required' => false,
                'label' => 'URI',
            ])

            ->add('display', 'checkbox', [
                'required' => false,
                'label' => 'Afficher le lien',
                'attr' => ['class' => 'checkbox'],
            ])

            ->add('displayChildren', 'checkbox', [
                'required' => false,
                'label' => 'Elément parent ?',
                'attr' => ['class' => 'checkbox'],
            ])

            ->add('parent', 'entity', [
                'class' => 'NodevoMenuBundle:Item',
                'empty_value' => ' - ',
                'required' => false,
                'label' => 'Fils de l\'item',
                'query_builder' => function (EntityRepository $er) use ($menuId) {
                    return $er->createQueryBuilder('item')
                              ->andWhere('item.display = 1')
                              ->leftJoin('item.menu', 'menu')
                                ->andWhere('menu.id = :idMenu')
                                ->setParameter('idMenu', $menuId)
                              ->orderBy('item.name');
                },
            ])

            ->add('menu', 'entity', [
                'class' => 'NodevoMenuBundle:Menu',
                'label' => 'Menu Associé',
                'empty_value' => ' - ',
                'required' => true,
            ])

            ->add('order', 'integer', [
                'label' => 'Ordre d\'affichage',
                'attr' => ['class' => $this->_constraints['order']['class']],
            ])

            ->add('selectIcon', 'choice', [
                    'label' => 'Type d\'icône',
                    'required' => false,
                    'mapped' => false,
                    'empty_value' => ' - ',
                    'choices' => [
                        'fa' => 'Fontawesome',
                        'glyphicon' => 'Glyphicon',
                    ],
            ])

            ->add('buttonIconGlyph', 'button', [
                    'label' => 'Icône',
                    'attr' => [
                        'class' => 'btn btn-success iconpicker',
                        'role' => 'iconpicker',
                        'data-placement' => 'right',
                        'data-rows' => 3,
                        'data-cols' => 6,
                        'data-iconset' => 'glyphicon',
                    ],
            ])
            ->add('buttonIconFontAwesome', 'button', [
                    'label' => 'Icône',
                    'attr' => [
                        'class' => 'btn btn-success iconpicker',
                        'role' => 'iconpicker',
                        'data-placement' => 'right',
                        'data-rows' => 3,
                        'data-cols' => 6,
                        'data-iconset' => 'fontawesome',
                    ],
            ])

            ->add('icon', 'hidden')
        ;

        $routeParamsModifier = function (FormInterface $form, $route) use ($datas) {
            $form->add('routeParameters', new ParamsType($route, $datas->getRouteParameters()), [
                'required' => false,
                'label' => 'Paramètres de la route sélectionnée',
                'mapped' => false,
                'data_class' => null,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($routeParamsModifier, $datas) {
                $route = isset($this->_allRoutes[$datas->getRoute()]) ? $this->_allRoutes[$datas->getRoute()] : null;
                $routeParamsModifier($event->getForm(), $route);
            }
        );

        $builder->get('route')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($routeParamsModifier) {
                $routeName = $event->getForm()->getData();
                $route = isset($this->_allRoutes[$routeName]) ? $this->_allRoutes[$routeName] : null;
                $routeParamsModifier($event->getForm()->getParent(), $route);
            }
        );
    }

    public function getName()
    {
        return 'nodevo_menu_item';
    }
}
