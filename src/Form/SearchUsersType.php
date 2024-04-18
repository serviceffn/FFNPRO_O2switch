<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver; 

class SearchUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('choice', ChoiceType::class, [
            'label'=>'Rechercher',
            'choices'  => [
                'Nom Seul' => 'nom',
                'Numéro Licence' => 'licence',
                'Nom & Prénom' => 'nomprenom',
                'Nom & Prénom & Date de Naissance(Année-Mois-Jour)' => 'nomprenomanniversaire',
            ]])
            ->add('nom', TextType::class, [
                'label' => False,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Rechercher...',
                ],
                'required' => false
            ])
            
            
          
            ->add('Rechercher', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            
        ]);
    }
}
