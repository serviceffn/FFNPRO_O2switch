<?php

namespace App\Form;

use App\Entity\Associations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints as Assert;

class DirigeantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dirigeant_president', null, [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Veuillez entrer un nom  avec des lettres et sans accents uniquements.',
                    ]),
                ],
            ])
            ->add('dirigeant_vice_president', null, [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Veuillez entrer un nom  avec des lettres et sans accents uniquements.',
                    ]),
                ],
            ])
            ->add('dirigeant_tresorier', null, [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Veuillez entrer un nom  avec des lettres et sans accents uniquements.',
                    ]),
                ],
            ])
            ->add('dirigeant_secretaire', null, [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Veuillez entrer un nom  avec des lettres et sans accents uniquements.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Associations::class,
        ]);
    }
}
