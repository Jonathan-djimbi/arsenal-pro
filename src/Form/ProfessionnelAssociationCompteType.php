<?php

namespace App\Form;

use App\Entity\ProfessionnelAssociationCompte;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfessionnelAssociationCompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raisonSocial', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Raison social *',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
            ])
            ->add('siret', NumberType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Numéro siret *',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
            ])
            ->add('noTVA', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Numéro TVA intercommunautaire',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
            ])
            ->add('typeFDO', ChoiceType::class, [
                'label' => 'FDO',
                'required' => false,
                'choices'=> [
                    'Gendarmerie' => '0',
                    'Police' => '1',
                    'Police municipale' => '2'
                ],
                'attr'=>[
                    'placeholder'=>'Type FDO',
                    'class' => 'border_radius_all box_shadow_all'
                ]
            ])
            ->add('numeroMatricule', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Numéro de matricule *',
                    'class' => 'border_radius_all box_shadow_all mb-4'
                ]
                ]);
            // ->add('submit', SubmitType::class, [
            //     'label' => 'Soumettre',
            //     'attr' => [
            //         'class' => 'btn-block btn-success box_shadow_all border_radius_all mx-auto w-75 mt-4'
            //     ]
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfessionnelAssociationCompte::class,
        ]);
    }
}
