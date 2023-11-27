<?php

namespace App\Form;

use App\Entity\CarteCadeau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarteCadeauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('code', TextType::class,[
            'label'=> 'Code de la carte cadeau',
            'required' => true,
            'attr'=> [
                'placeholder'=> 'Insérez le code...',
                'class'=>'form-control-sm',
                'size' => 20
             ]
        ])
        ->add('submit',SubmitType::class,[
            'label'=>'Valider le code',
            'attr'=>[
                'class'=>'btn btn-success btn-block'
            ]

        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarteCadeau::class,
        ]);
    }
}
