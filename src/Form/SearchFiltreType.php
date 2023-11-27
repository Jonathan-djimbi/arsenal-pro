<?php
namespace App\Form;

use App\Classe\Search;
use App\Entity\Calibre;
use App\Entity\Category;
use App\Entity\Famille;
use App\Entity\Marque;
use App\Entity\Produit;
use App\Entity\SubCategory;
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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Validator\Constraints\Regex;

class SearchFiltreType extends AbstractType
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
        if($min < 1){ //si un produit est inférieur à 1 euro (si 0, le filtre ne fonctionnera pas correctement)
            $min = $min + 1;
        }
        // dd($min);

        $builder
            //     ->add('string', TextType::class,
            //     [
            //         'label'=> false,
            //         'required' => false,
            //         'attr'=> [
            //             'placeholder'=> 'Recherche...',
            //             'class'=>'form-control-sm barderecherche',
            //             'size' => 130,
            //         ]
            //     ])
            //     ->add('rechercher', SubmitType::class,
            //     [
            //     'label' => 'R',
            //     'attr' => [
            //         'class' => 'btn-block btn-success buttonrechercher',
            //         'aria-label' => 'chercher'
            //         ]
            //     ]
            // )
            ->add('minprice', RangeType::class,
                [
                    'label' => false,
                    'required' => false,
                    'attr'=> [
                        //'placeholder'=> 'Minimum',
                        'class'=>'lesprix minprix align-self-start',
                        'max' => $max,
                        'min' => number_format($min,0),
                        'value' => number_format($min,0)
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
                    'min' => number_format($min,0),
                    'value' => $max,
                ],
                'constraints' => [
                    new Regex(array(
                        'pattern' => '/^[0-9]\d*$/',
                        'message' => 'Prix en chiffres.'
                        )
                    ),
                ]
            ])
            ->add('orderPrices',TextType::class,[
                'label'=> false,
                'required'=> false,
                // 'choices'=> [
                //     "Croissant" => "ASC",
                //     "Décroissant" => "DESC",
                // ],
                // 'multiple'=> false,
                // 'expanded'=> false,
                'attr' => [
                    'class' => 'orderPriceFiltre'
                ]
                
            ])            
            ->add('categories', EntityType::class,
            [
                'label' => false,
                'required' => false,
                'class' => Category::class,
                'placeholder' => 'Choissisez une catégorie',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-control form-control-categories',
                ],
                'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                    return $entityrepository->createQueryBuilder('c')
                        ->select('c','p')
                        ->join(join : 'c.produits', alias : 'p')
                        ->distinct('c.name')
                        ->andWhere('p.isAffiche = 1')                        
                        // ->andWhere('c.name != :prestation')->setParameter('prestation','Prestation')
                        ->orderBy('c.name','asc');
                },
            ])
            ->add('famille', EntityType::class,
            [
                'label' => false,
                'required' => false,
                'class' => Famille::class,
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-control familleFiltre',

                ],
                'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                    return $entityrepository->createQueryBuilder('f')
                        ->select('f')
                        ->distinct('f.name')                        
                        ->orderBy('f.name','asc');
                },
            ])
            ->add('subCategories', EntityType::class,
            [
                'label' => false,
                'required' => false,
                'class' => SubCategory::class,
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-control familleFiltre',

                ],
                'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                    return $entityrepository->createQueryBuilder('sb')
                        ->select('sb')
                        ->distinct('sb.name')                        
                        ->orderBy('sb.name','asc');
                },
            ])
            ->add('marques', EntityType::class,
            [
                'label' => false,
                'required' => false,
                'class' => Marque::class,
                'placeholder' => 'Choissisez une marque',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-control menuMarques form-control-categories',
                ],
                'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                    return $entityrepository->createQueryBuilder('m')
                        ->select('m','p')
                        ->join(join : 'm.produits', alias : 'p')
                        ->distinct('m.name')
                        ->andWhere('p.isAffiche = 1')
                        ->orderBy('m.name', 'ASC');
                },
            ])
            ->add('calibre', EntityType::class,
            [
                'label' => false,
                'required' => false,
                'class' => Calibre::class,
                'placeholder' => 'Choissisez votre calibre',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-control form-control-categories',
                ],
                'query_builder' => function (EntityRepository $entityrepository) { //pour faire du SQL dans un Form Type
                    return $entityrepository->createQueryBuilder('c')
                        ->select('c','p')
                        ->join(join : 'c.produit', alias : 'p')
                        ->distinct('c.calibre')
                        ->andWhere('c.calibre IS NOT NULL')
                        ->andWhere('p.isAffiche = 1')
                        ->orderBy('c.calibre', 'ASC');
                },
            ])->add('isOccasion', CheckboxType::class, [
                'label'    => 'Rechercher les occasions',
                'required' => false,
                'attr' => [
                    'class' => ''
                ]
            ])->add('isFDO', CheckboxType::class, [
                    'label'    => "Rechercher les forces de l'ordre",
                    'required' => false,
                    'attr' => [
                        'class' => ''
                ]
            ])->add('isPromo', CheckboxType::class, [
                'label'    => 'Les produits en promo',
                'required' => false,
                'attr' => [
                    'class' => ''
                ]
            ])


        ->add('submit', SubmitType::class,
            [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn-block btn-success w-100 mx-auto mt-2',
                    'aria-label' => 'filtrer'
                ]
            ]);
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

