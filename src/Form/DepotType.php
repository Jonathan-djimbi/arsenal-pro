<?php

namespace App\Form;

use App\Entity\DepotVente;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;


class DepotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class,[
            'label'=>'Nom *',
            'required'=>true,
            'attr'=>[
                'placeholder'=>'Entrez votre nom',
                'class' => 'box_shadow_all'
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
        ->add('prenom', TextType::class,[
            'label'=>'Prénom *',
            'required'=>true,
            'attr'=>[
                'placeholder'=>'Entrez votre prénom',
                'class' => 'box_shadow_all'
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
            ->add('dateNaissance', DateType::class,[
                'label'=> "Date de naissance *",
                'required'=>true,
                'widget' => 'single_text',
                'attr'=>[
                    'class' => 'box_shadow_all'
                ],
            ])
            ->add('adresse', TextType::class,[
                'label'=>"Adresse *",
                'attr'=>[
                    'class' => 'box_shadow_all'
                ],
            ])
            ->add('postal', TextType::class,[
                'label'=>'Code postal *',
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Entrez votre code postal',
                    'class' => 'box_shadow_all'
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
                    'label'=>'Ville *',
                    'required'=>true,
                    'attr'=>[
                        'placeholder'=>'Entrez votre ville',
                        'class' => 'box_shadow_all'
                    ],
                    'constraints' => array(
                        new Regex([
                            'pattern'=> '/^[a-zA-ZÀ-ÿ-]*$/',
                            'match'=> true,
                            'message' => 'La ville doit contenir que des lettres.',
                        ]),
                    )
            ])
            ->add('phone', TextType::class, [
                'label'=> 'Téléphone *',
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Votre numéro de téléphone',
                    'class' => 'box_shadow_all'
                ],
                'constraints' => array(
                    new Regex([
                        'pattern' => '/^[0-9]\d*$/',
                        'message' => 'Utilisez seulement des chiffres.'
                        ]
                    ),
                    new Length([
                        'max' => 10,
                        'min' => 9
                    ])
                )
            ])
            ->add('email', EmailType::class, [
                'label'=> 'Email *',
                'required'=>true,
                'constraints' => [
                        new Length([
                        'min'=>10,
                        'max'=>60
                    ]),
                    new Regex([
                        'pattern'=> '/^[a-zA-Z0-9][-\._a-zA-Z0-9]*@[a-zA-Z0-9][-\.a-zA-Z0-9]+$/',
                        'match'=> true,
                        'message' => "Votre adresse email ne doit pas contenir de caractères spéciaux et ni d'accents.",
                    ]),  
                ],
                'attr'=>[
                    'placeholder'=>'Votre adresse email *',
                    'class' => 'box_shadow_all mx-auto'
                ]
            ])
            ->add('nbArmePoing', NumberType::class, [
                'label'=> "Nombre d'armes de poing (pistolet, revolver ...) *",
                'required'=>true,
                'attr'=>[
                    'class' => 'box_shadow_all',
                    'min' => 0,
                    'value' => 0 //default
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9]\d*$/',
                        'message' => 'Utilisez seulement des chiffres.'
                        ])
                ],
            ])
            ->add('nbArmeEpaule', NumberType::class, [
                'label'=> "Nombre d'armes d'épaule (fusil, mousqueton ...) *",
                'required'=>true,
                'attr'=>[
                    'class' => 'box_shadow_all',
                    'min' => 0,
                    'value' => 0 //default
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9]\d*$/',
                        'message' => 'Utilisez seulement des chiffres.'
                        ])
                ],
            ])
            ->add('prixLot', TextType::class, [
                'label'=> "Pour le lot, j'en aimerais (fourchette basse + fourchette haute) *",
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'100-200€...',
                    'class' => 'box_shadow_all'
                ],
            ])
            ->add('photoUn', FileType::class,[
                'required'=>false,
                'label'=>false,
                'constraints' => [
                    new File([
                        'maxSize' => '6m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'box_shadow_all',
                    'accept' => 'image/jpg',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('photoDeux', FileType::class,[
                'required'=>false,
                'label'=>false,
                'constraints' => [
                    new File([
                        'maxSize' => '6m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'box_shadow_all',
                    'accept' => 'image/jpg',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('photoTrois', FileType::class,[
                'required'=>false,
                'label'=>false,
                'constraints' => [
                    new File([
                        'maxSize' => '6m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'box_shadow_all',
                    'accept' => 'image/jpg',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('photoQuatre', FileType::class,[
                'required'=>false,
                'label'=>false,
                'constraints' => [
                    new File([
                        'maxSize' => '6m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'box_shadow_all',
                    'accept' => 'image/jpg',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Indiquez tout ce que vous savez sur ces armes : les marques, modèles, calibres, années des armes et ce que vous en savez (utilisation, usure, état général et état canon, année d’achat ou possession) *",
                'required'=>true,
                'attr' => [
                    'class' => 'textarealimit box_shadow_all',
                    'maxLength' => 500
                ],
                'constraints' => [
                    new Length(array(
                        'max' => 500,
                        ))
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn-block btn-success border_radius_all mx-auto w-75 mt-4'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepotVente::class,
        ]);
    }
}
