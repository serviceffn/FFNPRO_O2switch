<?php

namespace App\Form;

use App\Entity\Associations;
use App\Entity\Regions;
use App\Entity\Users;
use Doctrine\DBAL\Types\DateType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType as TypeDateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class UsersEditType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder


            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\s\-]+$/u',
                        'message' => 'Veuillez entrer un nom valide.',
                    ]),
                ],
            ])

            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom ne peut pas être vide.']),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\s\-]+$/u',
                        'message' => 'Veuillez entrer un prénom valide.',
                    ]),
                ],
            ])



            ->add('anniversaire', TypeDateType::class, [
                'widget' => 'single_text',
                // this is actually the default format for single_text
                'format' => 'yyyy-MM-dd',
            ])
            // ->add('centre_emetteur')
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Masculin' => 'Masculin',
                    'Feminin' => 'Feminin',
                ],
            ])
            ->add('centre_emetteur', EntityType::class, [
                'class' => Associations::class,
                'label' => 'Filtrer par Centre/Association',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC');
                },
                // 'style' => 'margin:20%' 
            ])
            ->add('region', EntityType::class, [
                'class' => Regions::class,
                'label' => false,
                // 'style' => 'margin:20%' 
            ])
            // ->add('n_licence', HiddenType::class,[
            //     'data' => null,
            // ])
            ->add('adresse')
            ->add(
                'complement',
                null,
                array(
                    'required' => false
                )
            )
            ->add('zip')
            ->add('ville')
            ->add('pays')
            ->add(
                'telephone',
                null,
                array(
                    'required' => false
                )
            )
            ->add(
                'email',
                null,
                array(
                    'required' => false
                )
            )
            ->add('agree_terms', ChoiceType::class, [
                'choices' => [
                    'Oui' => '1',
                    'Non' => '0',
                ],
            ])
            ->add('is_active', HiddenType::class, [
                'data' => 1,
            ])
        ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
