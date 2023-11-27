<?php

namespace App\Form;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteDocumentsVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cartIdcheck', CheckboxType::class, [
                'required' => false,
                'disabled'=>false,
                'label'=> 'Vérification CNI',
            ])
            ->add('licenceTirIdcheck', CheckboxType::class, [
                'required' => false,
                'disabled'=>false,
                'label'=> 'Vérification licence de tir',
            ])
            ->add('certificatMedicalIdcheck', CheckboxType::class, [
                'required' => false,
                'disabled'=>false,
                'label'=> 'Vérification certificat médical',
            ])
            ->add('cartPoliceIdcheck', CheckboxType::class, [
                'required' => false,
                'disabled'=>false,
                'label'=> 'Vérification carte de police',
            ])
            ->add('numero_sia_check', CheckboxType::class, [
                'required' => false,
                'disabled'=>false,
                'label'=> 'Vérification numéro SIA',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'disabled'=>false,
                'label'=> 'Message au client (optionnel) : ',
                'attr' => [ 
                    'class' => 'mx-auto',
                    'placeholder' => 'Remarque au client...'
                ],
            ])
            ->add('submit',SubmitType::class, [
                'label'=>"Vérifier les documents",
                'disabled' => false,
                'attr' => [ 
                    'class' => 'btn btn-success border_radius_all',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}
