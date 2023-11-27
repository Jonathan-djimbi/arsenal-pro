<?php

namespace App\Form;

use App\Entity\HistoriqueReservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderDetailsReservationActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reservationPourLe', DateType::class,[
                'label'=> "Réserver pour le (les dates peuvent être changées par l'armurier) *",
                'required'=>true,
                'widget' => 'single_text',
                'attr'=>[
                    'class' => 'box_shadow_all'
                ],
            ])
            ->add('typeFormation',ChoiceType::class,[
                'label'=> 'Type de réservation',
                'required'=> true,
                'choices'=> [
                    "Fondamental F" => 0,
                    "Basic B" => 1,
                    "Expert E" => 2,
                ],
                'multiple'=> false,
                'expanded'=> false,
                'attr' => [
                    'style'=> 'max-width: 100%; width: 100%; max-height: 100%; height: 40px; border-radius: 10px;',
                    'class' => 'box_shadow_all'
                ]
                
            ])
            ->add('submit', SubmitType::class, [
                'label'=>"Valider la réservation",
                'disabled' => false,
                'attr' => [ 
                    'class' => 'mx-auto d-block btn btn-success border_radius_all',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueReservation::class,
        ]);
    }
}
