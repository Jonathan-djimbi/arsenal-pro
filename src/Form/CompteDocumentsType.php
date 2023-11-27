<?php

namespace App\Form;

use App\Entity\ComptesDocuments;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Date;

class CompteDocumentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('cartId',FileType::class,[
                'required'=>true,
                'disabled'=>false,
                'label'=>'Carte nationale (RECTO ET VERSO) *',
                'constraints' => [
                    new File([
                        'maxSize' => '4m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'border_radius_all box_shadow_all_form',
                    'accept' => 'image/jpg',
                    'accept' => 'image/png',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('cartIdDate',DateType::class,[
                'required'=>true,
                'disabled'=>false,
                'label'=>'Date de validité *',
                'widget' => 'single_text', //pour avoir un plus beau rendu du calendrier
                'attr' => [
                    'class' => 'iddatecompte border_radius_all box_shadow_all_form'
                ]
            ])
            ->add('licenceTirId',FileType::class,[
                'required'=>false,
                'disabled'=>false,
                'label'=>'Certificat de licence de tir',
                'constraints' => [
                    new File([
                        'maxSize' => '4m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                    ],
                    'attr' => [
                        'class' => 'border_radius_all box_shadow_all_form',
                        'accept' => 'image/jpg',
                        'accept' => 'image/png',
                        'accept' => 'image/jpeg'

                    ]
            ])
            ->add('licenceTirIdDate',DateType::class,[
                'required'=>false,
                'disabled'=>false,
                'label'=>'Date de validité',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'iddatecompte border_radius_all box_shadow_all_form',
                ]
            ])

            ->add('certificatMedicalId',FileType::class,[
                'required'=>false,
                'disabled'=>false,
                'label'=>'Certificat médical valable',
                'constraints' => [
                    new File([
                        'maxSize' => '4m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'border_radius_all box_shadow_all_form',
                    'accept' => 'image/jpg',
                    'accept' => 'image/png',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('certificatMedicalIdDate',DateType::class,[
                'required'=>false,
                'disabled'=>false,
                'label'=>'Date de validité',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'iddatecompte border_radius_all box_shadow_all_form'
                ]
            ])
            ->add('cartPoliceId',FileType::class,[
                'required'=>false,
                'disabled'=>false,
                'label'=>'Carte de police valable (recto et verso) *',
                'constraints' => [
                    new File([
                        'maxSize' => '4m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG.',
                    ])
                ],
                'attr' => [
                    'class' => 'border_radius_all box_shadow_all_form',
                    'accept' => 'image/jpg',
                    'accept' => 'image/png',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('numero_sea', TextType::class, [
                'required'=>true,
                'label' => "Votre numéro SIA",
                'attr' => [
                    'placeholder' => 'Numéro SIA',
                    'class' => 'border_radius_all box_shadow_all mb-4',
                    'onkeyup' => "clientNoNum(this, 1)"
                ]
            ])
            ->add('justificatifDomicile',FileType::class,[
                'required'=>false,
                'disabled'=>false,
                'label'=>'Justificatif de domicile de -3 mois',
                'constraints' => [
                    new File([
                        'maxSize' => '4m',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Vous devez donner un justificatif en format PNG, JPG ou JPEG.',
                        'maxSizeMessage' => 'Votre justificatif de domicile ne doit pas dépasser 4 MB.',
                    ])
                ],
                'attr' => [
                    'class' => 'border_radius_all box_shadow_all_form',
                    'accept' => 'image/jpg',
                    'accept' => 'image/png',
                    'accept' => 'image/jpeg'
                ]
            ])
            ->add('submit',SubmitType::class, [
                'label'=>"Envoyer mes documents",
                'disabled' => false,
                'attr' => [ 
                    'class' => 'btn btn-success border_radius_all',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComptesDocuments::class,
        ]);
    }
}
