<?php
namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use App\Entity\Marque;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\ChoiceList\ChoiceList;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Validator\Constraints\Regex;

class SearchSuisseType extends AbstractType
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {                
        $prixmax = $this->entityManager->getRepository(Produit::class)->findPrix('MAX',1);
        $prixmin = $this->entityManager->getRepository(Produit::class)->findPrix('MIN',1);
        $max = intval($prixmax[0][1])/100;
        $min = intval($prixmin[0][1])/100;

        $builder
            ->add('string', TextType::class,
                [
                    'label'=> false,
                    'required' => false,
                    'attr'=> [
                        'placeholder'=> 'Recherche...',
                        'class'=>'form-control-sm barderecherche',
                        'size' => 130
                     ]
                ])
                ->add('rechercher', SubmitType::class,
                [
                    'label' => 'R',
                    'attr' => [
                        'class' => 'btn-block btn-success buttonrechercher',
                        'aria-label' => 'chercher'
                    ]
                ])
                ->add('minprice', RangeType::class,
                    [
                        'label' => false,
                        'required' => false,
                        'attr'=> [
                            //'placeholder'=> 'Minimum',
                            'class'=>'lesprix minprix align-self-start',
                            'max' => $max,
                            'min' => $min,
                            'value' => $min
                        ],
                        'constraints' => [
                            new Regex(array(
                                'pattern' => '/^[0-9]\d*$/',
                                'message' => 'Prix en chiffres.'
                                )
                            ),
                        ]
                    ]
                )
                ->add('maxprice', RangeType::class,
                [
                    'label' => false,
                    'required' => false,
                    'attr'=> [
                        //'placeholder'=> 'Maximum',
                        'class'=>'lesprix maxprix align-self-start',
                        'max' => $max,
                        'min' => $min,
                        'value' => $max
                    ],
                    'constraints' => [
                        new Regex(array(
                            'pattern' => '/^[0-9]\d*$/',
                            'message' => 'Prix en chiffres.'
                            )
                        ),
                    ]
                ]
            )
                ->add('categories', EntityType::class,
                [
                    'label' => false,
                    'required' => false,
                    'class' => Category::class,
                    'placeholder' => 'Choissisez une catégorie',
                    'multiple' => true,
                    'expanded' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                        return $entityrepository->createQueryBuilder('c')
                            ->select('c','p')
                            ->join(join : 'c.produits', alias : 'p')
                            ->distinct('c.name')
                            ->andWhere('p.isAffiche = 1')
                            ->andWhere('p.isSuisse = 1')                        
                            ->andWhere('c.name != :prestation')->setParameter('prestation','Prestation')
                            ->orderBy('c.name','asc');
                    },
                ])
                ->add('marques', EntityType::class,
                [
                    'label' => false,
                    'required' => false,
                    'class' => Marque::class,
                    'placeholder' => 'Choissisez une marque',
                    'multiple' => true,
                    'expanded' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                        return $entityrepository->createQueryBuilder('m')
                            ->select('m','p')
                            ->join(join : 'm.produits', alias : 'p')
                            ->distinct('m.name')
                            ->andWhere('p.isAffiche = 1')
                            ->andWhere('p.isSuisse = 1')
                            ->orderBy('m.name', 'ASC');
                    },
                ])

            // ->add('categories', ChoiceType::class, [
            //     'choices'  =>
            //         function (?Category $entity) {
            //             return $entity ? $entity->getName() : '';
            //         },
            // ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Filtrer',
                    'attr' => ['class' => 'btn-block btn-success border_radius_all w-75 mx-auto mt-3']
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

