<?php

namespace App\Form;

use App\Entity\Adress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;



class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          /*  ->add('brochure', FileType::class, [
                'label' => 'Brochure (PDF file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '5000M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
             */

            ->add('name', TextType::class,[
                'label'=>'Quel nom souhaitez-vous donner à votre adresse ?',
                'attr'=>[
                    'placeholder'=>'Indiquez votre adresse',
                    'class' => 'border_radius_all box_shadow_all'
                ]
            ])
            ->add('firstname', TextType::class,[
                'label'=>'Votre prénom ?',
                'attr'=>[
                    'placeholder'=>'Entrez votre prénom',
                    'class' => 'border_radius_all box_shadow_all'
                ],
                'constraints' => [
                    new Length([
                        'min'=>2,
                        'max'=>30
                    ]),
                    new Regex([
                        'pattern'=> '/^[a-zA-ZÀ-ÿ-]*$/',
                        'match'=> true,
                        'message' => 'Votre prénom doit contenir que des lettres.',
                    ]),
                ],
                ])
            ->add('lastname', TextType::class,[
                'label'=>'Votre nom ?',
                'attr'=>[
                    'placeholder'=>'Entrez votre nom',
                    'class' => 'border_radius_all box_shadow_all'
                ],
                'constraints' => [
                    new Length([
                        'min'=>2,
                        'max'=>30
                    ]),
                    new Regex([
                        'pattern'=> '/^[a-zA-ZÀ-ÿ-]*$/',
                        'match'=> true,
                        'message' => 'Votre nom doit contenir que des lettres.',
                    ]),
                ],
                ])
            ->add('company', TextType::class,[
                'label'=>'Votre société ?',
                'required'=>false,
                'attr'=>[
                    'placeholder'=>'Entrez le nom de votre société (facultatif)',
                    'class' => 'border_radius_all box_shadow_all'
                ]

                ])
            ->add('adress', TextType::class,[
                'label'=>'Votre adresse ',
                'attr'=>[
                    'placeholder'=>'8 rue des Paris...',
                    'class' => 'border_radius_all box_shadow_all'
                    ]
                ])
            ->add('postal', TextType::class,[
                    'label'=>'Votre code postal ?',
                    'attr'=>[
                        'placeholder'=>'Entrez votre code postal',
                        'class' => 'border_radius_all box_shadow_all'
                    ],
                    'constraints' => array(
                        new Regex(array(
                            'pattern' => '/^[0-9]\d*$/',
                            'message' => 'Utilisez seulement des chiffres.'
                            )
                        ),
                        new Length(array(
                            'max' => 10,
                            'min' => 3
                            ))
                    )
            ])
            ->add('city', TextType::class,[
                    'label'=>'Votre ville ?',
                    'attr'=>[
                        'placeholder'=>'Entrez votre ville',
                        'class' => 'border_radius_all box_shadow_all'
                    ],
                    'constraints' => array(
                        new Regex([
                            'pattern'=> '/^[a-zA-ZÀ-ÿ-]*$/',
                            'match'=> true,
                            'message' => 'La ville doit contenir que des lettres.',
                        ]),
                    )
            ])
            ->add('country', ChoiceType::class, [
                'label'=>'Votre pays ?',
                'choices'=> [
                    'FR' => 'FR',
                    'CH' => 'CH',
                    'BE' => 'BE'
                ],
                'attr'=>[
                    'placeholder'=>'Entrez votre pays',
                    'class' => 'border_radius_all box_shadow_all'
                ]
            ])
            ->add('phone', TelType::class,[
                'label'=>'Votre numéro de téléphone ?',
                'attr'=>[
                    'placeholder'=>'Entrez un numéro de contact',
                    'class' => 'border_radius_all box_shadow_all'
                ],
                'constraints' => array(
                    new NotBlank(), 
                    new Regex(array(
                        'pattern' => '/^[0-9]\d*$/',
                        'message' => 'Utilisez seulement des chiffres.'
                        )
                    ),
                    new Length(array(
                        'max' => 10,
                        'min' => 10
                        ))
                )
            ])
            ->add('submit',SubmitType::class,[
                'label'=>'Ajouter mon adresse',
                'attr'=>[
                    'class'=> 'btn-block w-75 btn-success border_radius_all mx-auto mt-4'
                ]

            ])
            
          
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adress::class,
        ]);
    }
}
