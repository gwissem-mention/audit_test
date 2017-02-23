<?php
namespace HopitalNumerique\ObjetBundle\Form;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContenuType extends AbstractType
{
    private $constraints = [];

    /**
     * @var ObjetManager ObjetManager
     */
    private $objetManager;


    /**
     * ContenuType constructor.
     *
     * @param              $manager
     * @param              $validator
     * @param ObjetManager $objetManager
     */
    public function __construct($manager, $validator, ObjetManager $objetManager)
    {
        $this->constraints  = $manager->getConstraints($validator);
        $this->objetManager = $objetManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var Contenu
         */
        $contenu = $builder->getData();
        
        /**
         * @var User
         */
        $user = $options['user'];

        $objetsOptions = [
            'mapped' => false,
            'choices' => [],
            'label' => 'Productions liées',
            'multiple' => true,
            'attr' => [
                'class' => 'select2'
            ]
        ];
        if (null !== $builder->getData()->getObjets()) {
            $objetsOptions['data'] = $builder->getData()->getObjets()->toArray();
        }

        $builder
            ->add('titre', TextType::class, [
                'max_length' => $this->constraints['titre']['maxlength'],
                'required'   => true,
                'label'      => 'Titre',
                'attr'       => [
                        'class' => $this->constraints['titre']['class']
                    ],
            ])
            ->add('alias', TextType::class, [
                'max_length' => $this->constraints['alias']['maxlength'],
                'required'   => false,
                'label'      => 'Alias',
                'attr'       => [
                    'class' => $this->constraints['alias']['class']
                ],
            ])
            ->add('contenu', TextareaType::class, [
                'required' => true,
                'label'    => 'Contenu',
                'attr'     => [
                    'class' => $this->constraints['contenu']['class']
                ],
            ])
            ->add('modified', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('objets', ChoiceType::class, $objetsOptions)
            ->add('domaines', EntityType::class, [
                'class' => 'HopitalNumerique\DomaineBundle\Entity\Domaine',
                'choices' => $user->getDomaines(),
                'label' => 'Domaines',
                'multiple' => true,
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('types', EntityType::class, [
                'class' => 'HopitalNumeriqueReferenceBundle:Reference',
                'property' => 'libelle',
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) use ($contenu) {
                    $qb = $er->createQueryBuilder('ref');
                    if ($contenu->getObjet()->isArticle()) {
                        $qb->andWhere('ref.id != 188', 'ref.id != 570', 'ref.code = :article')
                           ->setParameter('article', 'CATEGORIE_ARTICLE');
                    } elseif (!$contenu->getObjet()->isArticle()) {
                        $qb->andWhere('ref.id != 175', 'ref.code = :objet')
                           ->setParameter('objet', 'CATEGORIE_OBJET');
                    }
                    $qb->orderBy('ref.order', 'ASC');
                    return $qb;
                },
                'attr' => [
                    'class' => 'select2'
                ]
            ])
        ;
    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'HopitalNumerique\ObjetBundle\Entity\Contenu',
            ])
            ->setRequired(['domaine', 'user'])->setAllowedTypes([
                'domaine' => 'HopitalNumerique\DomaineBundle\Entity\Domaine',
                'user'    => 'HopitalNumerique\UserBundle\Entity\User',
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hopitalnumerique_objet_contenu';
    }
}
