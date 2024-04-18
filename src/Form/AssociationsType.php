<?php

namespace App\Form;

use App\Entity\Associations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles')
            ->add('password', HiddenType::class,[
                'data' => '$argon2id$v=19$m=16,t=2,p=1$MEc3YmFjUEE1MkkydjZNMw$T4FOcbw5UMO6quIyoSRfsQ',
            ])
            ->add('nom')
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Region' => 'Region',
                    'Centre' => 'Centre',
                    'Association' => 'Association',
                ],
            ])
            ->add('adresse')
            ->add('initiale')
            ->add('zip')
            ->add('ville')
            ->add('pays')
            ->add('nom_presidebt')
            ->add('prenom_president')
            ->add('email_president')
            ->add('email_assoc')
            ->add('telephone_president')
            ->add('telephone_assoc')
            ->add('is_active', HiddenType::class,[
                'data' => 1,
            ])
            ->add('region')
                ->add('images', FileType::class,[
                    'label' => false,
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false
                ],  array('attr' => array('style' => 'width:10%'))
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Associations::class,
        ]);
    }
}
