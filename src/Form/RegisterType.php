<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

use function Sodium\add;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label'=> false,
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
                'attr'=>[
                    'placeholder'=>'Votre prénom *',
                    'class' => 'border_radius_all box_shadow_all mx-auto'
                ]

            ])
            ->add('lastname', TextType::class, [
                'label'=> false,
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
                'attr'=>[
                    'placeholder'=>'Votre nom *',
                    'class' => 'border_radius_all box_shadow_all mx-auto'
                ]
            ])
            ->add('email', EmailType::class, [
                'label'=> false,
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
                    'class' => 'border_radius_all box_shadow_all mx-auto'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type'=> PasswordType::class,
                'invalid_message'=>'Le mot de passe et la confirmation doivent être identiques',
                // 'label'=>'Votre mots de passe',
                'constraints' => [ 
                    new Regex([
                        'pattern' => '/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[\W])([a-zA-Z0-9À-ÿ\W]+)$/', //au moin un caractère spécial, un numéro et une lettre ex : (?=.*) groupage regex
                        'match' => true,
                        'message' => 'Utilisez au moins un chiffre, un caractère spécial (#?&!$€*@) et une lettre.'
                    ]),
                    new Length([
                        'min'=>6,
                        'max'=>60,
                    ]),
                ],
                'required'=> true,
                'first_options'=>['label'=> false,
                    'attr'=>[
                        'placeholder'=>'Votre mot de passe *',
                        'class' => 'border_radius_all box_shadow_all mx-auto'
                    ]
                ],



                'second_options'=>['label'=> false,
                    'attr'=>[
                        'placeholder'=>'Confirmez votre mot de passe *',
                        'class' => 'border_radius_all box_shadow_all mx-auto'
                    ]
                ]
            ])
            ->add('submit',SubmitType::class, [
                'label'=>"S'inscrire",
                'attr' => [
                    'class' => 'btn btn-info d-block w-50 mt-3 border_radius_all mx-auto btn-lg'
                ],
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
