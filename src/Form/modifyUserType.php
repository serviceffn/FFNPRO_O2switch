<?php

namespace App\Form;

use App\Entity\Associations;
use App\Entity\Regions;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class modifyUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles')
            // ->add('password')
            ->add('nom')
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Club' => 'Club',
                    'Centre' => 'Centre',
                    'Association' => 'Association',
                ],
            ])
            ->add('adresse')
            
            ->add('zip')
            ->add('ville')
            ->add('pays')
            ->add('nom_presidebt')
            ->add('prenom_president')
            ->add('email_president')
            ->add('email_secretaire_general', null, [
                'label' => 'Email du Secrétaire Général'
            ])
            ->add('email_tresorier', null, [
                'label' => 'Email du Trésorier'
            ])
            ->add('email_assoc', null, array(
                'required' => false
            ))  
            ->add('initiale')
            ->add('telephone_president')
            ->add('telephone_assoc')
            ->add('is_active', HiddenType::class,[
                'data' => 1,
            ])
            ->add('region', EntityType::class, [
                'class' => Regions::class,
                'label' => false,
                // 'style' => 'margin:20%' 
            ])
            // ->add('region')
            ->add('images', FileType::class,[
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ],  array('attr' => array('style' => 'width:10%'))
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Associations::class,
        ]);
    }
}
