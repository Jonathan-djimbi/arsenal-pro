<?php

namespace App\Form;

use App\Entity\Adress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class CarteFideliteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
        ->add('adress',EntityType::class,[
            'label'=>'Adresse : ',
            'required'=> true,
            'class'=>Adress::class,
            'choices'=> $user->getadresses(),
            'multiple'=> false,
            'expanded'=> false,
            'attr' => [
                'style'=> 'height: 40px !important; max-width: 90%;',
                'class' => 'inputCarteFidelite, inputCarteFideliteCreation'
            ]
            
        ])
        ->add('specialite', ChoiceType::class, [
            'label'=>'Specialité : ',
            'choices'=> [
                'Pistolet' => 'Pistolet',
                'Carabine' => 'Carabine',
            ],
            'attr'=>[
                'placeholder'=>'-',
                'class' => 'inputCarteFidelite, inputCarteFideliteCreation',
                'style'=> 'height: 40px !important; max-width: 90%;'

            ]
        ])
        ->add('telephone', TelType::class,[
            'label'=>'Téléphone : ',
            'required'=> true,
            'attr'=>[
                'placeholder'=>'Entrez un numéro de contact',
                'class' => 'inputCarteFidelite, inputCarteFideliteCreation',
                'style' => "width: 90%;",
                'maxLength' => 10,
            ],
            'constraints' => array(
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
        ->add('club', TextType::class, [
            'label' => 'Club de tir : ',
            'required'=> false,
            'attr' => [
                'placeholder' => 'Votre club',
                'class' => 'inputCarteFidelite, inputCarteFideliteCreation',
                'style' => "width: 90%;",
            ],
            'constraints' => [
                new Length(array(
                    'max' => 100,
                    'min' => 2
                    ))
            ]
        ])
        ->add('photo',FileType::class,[
            'required'=>false,
            'disabled'=>false,
            'label' => 'Modifier',
            'label_attr' => [
                'class' => 'custom_upload_image_but',
            ],
            'constraints' => [
                new File([
                    'maxSize' => '4m',
                    'mimeTypes' => [
                        'image/jpg',
                        'image/jpeg'
                    ],
                    'mimeTypesMessage' => 'Vous devez donner un fichier PNG, JPG ou JPEG',
                ])
            ],
            'attr' => [
                'accept' => 'image/jpg',
                'accept' => 'image/jpeg',
                'onchange' => 'afficherUploadImageBeta(event)',
                'id' => "upload_image",
                'style' => 'display: none;'
            ]
        ])
        ->add('submit',SubmitType::class,[
            'label'=>'Mettre à jour ma carte',
            'attr'=>[
                'class'=>'mt-3 btn btn-success btn-block border_radius_all_15 w-50 mx-auto box_shadow_all mb-3'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user'=>  array(),
        ]);
    }
}
