<?php

namespace App\Form;

use App\Entity\Associations;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchByAssociations extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         
        $builder->add('nom', EntityType::class, [
            'class' => Associations::class,
            'label' => 'Filtrer par Centre/Association',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.nom', 'ASC');
            },
            
           
            // 'style' => 'margin:20%' 
        ])
        ->add('Rechercher', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary',
                'style' => ''
            ]
        ]);
             
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Associations::class,
        ]);
    }
}
