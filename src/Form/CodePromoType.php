<?php

namespace App\Form;

use App\Entity\CodePromo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CodePromoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void //OUTDATED////////////////////////////
    {
        $builder
        ->add('code', TextType::class,[
            'label'=> 'Code promo',
            'required' => false,
            'attr'=> [
                'placeholder'=> 'Code promo...',
                'class'=>'form-control-sm',
                'size' => 20
             ]
        ])
        ->add('submit',SubmitType::class,[
            'label'=>'Valider',
            'attr'=>[
                'class'=>'btn btn-secondary btn-block'
            ]

        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CodePromo::class,
        ]);
    }
}
