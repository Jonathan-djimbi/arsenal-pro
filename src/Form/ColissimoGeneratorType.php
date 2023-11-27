<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColissimoGeneratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('utilisateur',EntityType::class,[
            'label'=> 'Utilisateur',
            'required'=> true,
            'class'=>User::class,
            'multiple'=> false,
            'expanded'=> false,
            'attr' => [
                'class' => 'lesUsers'
            ]
        ])
        ->add('masse', NumberType::class, [
            'label'=> "Poids en kilogramme *",
            'required'=>true,
            'attr'=>[
                'placeholder'=>'kg',
                'class' => 'border_radius_all box_shadow_all',
                'min' => 0,
                'step' => 0.01,
            ],
        ])
        ->add('insuranceValue', ChoiceType::class, [
            'label' => "Prix de l'assurance en euros *",
            'required' => true,
            'choices'=> [
                '0' => '0',
                '150' => '15000',
                '300' => '30000',
                '500' => '50000',
                '1000' => '100000',
                '2000' => '200000',
                '5000' => '500000'
            ],
            'attr'=>[
                'placeholder'=>'Assurance',
                'class' => 'border_radius_all box_shadow_all'
            ]
        ])
        ->add('submit',SubmitType::class, [
            'label'=>"Génerer le ticket Colissimo",
            'disabled' => false,
            'attr' => [ 
                'class' => 'btn btn-success border_radius_all d-block mx-auto',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
