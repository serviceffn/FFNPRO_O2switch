<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver; 

class DematerialisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('impression', ChoiceType::class, [
            'choices'  => [
                'Imprimez ma licence' => '1',
                // 'Seulement le QRCode' => '0',
            ],
        ])
          
            ->add('Renouveler la licence', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                    'style' => 'display: block;width: 220px;margin: 50px auto;'
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
