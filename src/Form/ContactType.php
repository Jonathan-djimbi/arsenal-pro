<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre prénom *',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre nom *',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre adresse email *',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
            ])
            ->add('phone', TelType::class, [
                'label'=> false,
                'required'=>false,
                'attr'=>[
                    'placeholder'=>'Votre numéro de téléphone',
                    'class' => 'border_radius_all box_shadow_all'
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
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'En quoi pouvons-nous vous aider ? *',
                    'class' => 'textarealimit box_shadow_all',
                    'maxLength' => 500
                ],
                'constraints' => array(
                    new Length(array(
                        'max' => 500,
                        ))
                )
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
            'data_class' => Contact::class,
        ]);
    }
}
