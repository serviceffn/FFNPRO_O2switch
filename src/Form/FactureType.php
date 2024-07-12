<?php

namespace App\Form;

use App\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isDeposerAction = $options['is_deposer_action'];

        $builder
            ->add('pdfContent', FileType::class, [
                'label' => 'Fichier PDF',
                'mapped' => false,
                'required' => $isDeposerAction,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF valide',
                    ]),
                    $isDeposerAction ? new NotBlank(['message' => 'Veuillez télécharger un fichier PDF']) : null,
                ],
            ])
            ->add('pdfFilename', TextType::class, [
                'label' => 'Nom du fichier PDF',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
            'is_deposer_action' => false,
        ]);
    }
}
