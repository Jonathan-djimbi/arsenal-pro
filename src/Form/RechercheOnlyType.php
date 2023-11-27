<?php
namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use App\Entity\Marque;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\ChoiceList\ChoiceList;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Regex;

class RechercheOnlyType extends AbstractType
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {                

        $builder
            ->add('string', TextType::class,
                [
                    'label'=> false,
                    'required' => false,
                    'attr'=> [
                        'placeholder'=> 'Recherche...',
                        'class'=>'form-control-sm barderecherche',
                        'size' => 130
                     ]
                ])
                ->add('rechercher', SubmitType::class,
                [
                    'label' => 'R',
                    'attr' => [
                        'class' => 'btn-block btn-success buttonrechercher',
                        'aria-label' => 'chercher'
                        ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

