<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\Carrier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderPrestationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {         
        
       
        $user = $options ['user'];
        $builder
            ->add('adresses',EntityType::class,[
                'label'=> 'Confirmez vos informations',
                'required'=> true,
                'class'=>Adress::class,
                'choices'=> $user->getadresses(),
                'multiple'=> false,
                'expanded'=> false,
                'attr' => [
                    'style'=> 'max-width: 100%; width: 100%; max-height: 100%; height: 40px; border-radius: 10px;',
                    'class' => 'box_shadow_all'
                ]
                
            ])
            ->add('code', TextType::class,[
                'label'=> 'Utiliser un code promo',
                'required' => false,
                'attr'=> [
                    'placeholder'=> 'Code promo...',
                    'class'=>'form-control-sm border_radius_all box_shadow_all col-md-6',
                    'size' => 20
                 ]
            ])
            ->add('fidele', CheckboxType::class,[
                'label' => "Utiliser vos points de fidélité ? (à partir de 200€)",
                'required' => false,
            ])
            ->add('sommeCompte', IntegerType::class,[
                'required' => false,
                'attr'=> [
                    'placeholder'=> '10...',
                    'class'=>'form-control-sm border_radius_all box_shadow_all col-md-6',
                    'type' => 'number',
                    'size' => 20,
                    'step' => 0.01,
                ],
            ])
            ->add('submit',SubmitType::class,[
                'label'=>'Valider la commande',
                'attr'=>[
                    'class'=>'btn btn-success btn-block mx-auto border_radius_all'
                ]

            ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'user'=>  array()
        ]);
    }
}
