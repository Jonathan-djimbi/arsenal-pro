<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,[
                'disabled'=>true,
                'attr'=>[
                    'class' => 'box_shadow_all border_radius_all text-center'
                ]
            ])
            ->add('firstname',TextType::class,[
                'disabled'=>true,
                'label'=>'Mon prénom',
                'attr'=>[
                    'class' => 'box_shadow_all border_radius_all text-center'
                ]
            ])
            ->add('lastname',TextType::class,[
                'disabled'=>true,
                'label'=>'Mon nom',
                'attr'=>[
                    'class' => 'box_shadow_all border_radius_all text-center'
                ]
            ])
            ->add('old_password', PasswordType::class,[
                'label'=>'Mon mot de passe actuel *',
                'mapped'=>false,
                'attr'=>[
                    'placeholder'=>'Veuillez saisir votre mot de passe actuel',
                    'class' => 'box_shadow_all border_radius_all'
                ]
            ])

            ->add('new_password', RepeatedType::class, [
                'type'=> PasswordType::class,
                'mapped'=>false,

                'invalid_message'=>'Le mot de passe et la confirmation doivent êtres identique',
                'label'=>'Mon nouveau mot de passe *',
                'constraints' => [ 
                    new Length([
                        'min'=>6,
                        'max'=>60
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[\W])([a-zA-Z0-9À-ÿ\W]+)$/', //au moin un caractère spécial, un numéro et une lettre ex : (?=.*) groupage regex
                        'match' => true,
                        'message' => 'Utilisez au moins un chiffre, un caractère spécial et une lettre.'
                    ]) 
                ],
                'required'=> true,
                'first_options'=>['label'=>' Mon nouveau mot de passe *',
                    'attr'=>[
                        'placeholder'=>'Merci de saisir votre nouveau mot de passe',
                        'class' => 'box_shadow_all border_radius_all'
                    ]
                ],



                'second_options'=>['label'=>'Confirmez votre nouveau mot de passe *',
                    'attr'=>[
                        'placeholder'=>'Merci de confirmez votre nouveau mot de passe',
                        'class' => 'box_shadow_all border_radius_all'
                    ]
                ]
            ])
            ->add('submit',SubmitType::class, [
                'label'=>"Soumettre",
                'attr' => [ 'class' => 'mx-auto btn btn-info d-block mt-4 border_radius_all w-50']
            ])
        ;



    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
