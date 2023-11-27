<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class RefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('prix', NumberType::class, [
            'label'=> "Prix TTC remboursé (en euros, un point pour séparer les centimes)",
            'required'=>true,
            'attr'=>[
                'placeholder'=>'...€',
                'class' => 'border_radius_all box_shadow_all_form mx-auto',
                'min' => 0,
                'step' => 0.01,
                'style' => 'max-width: 500px; width: 100%;'
            ],
            'constraints' => array(
                new Regex([
                    'pattern' => '/^[-]?([0-9]*[.])?[0-9]+$/',
                    'message' => 'Utilisez seulement des chiffres (en nombre et un point pour les centimes).'
                    ]
                ),
            )
        ])
        ->add('submit',SubmitType::class, [
            'label'=>"Valider le remboursement",
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
