<?php

namespace App\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
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
use Doctrine\ORM\EntityManagerInterface;

class UsersType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Zàâäéèêëìîïôöùûüç\s\-]+$/u',
                        'message' => 'Veuillez entrer un nom valide.',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom ne peut pas être vide.']),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Zàâäéèêëìîïôöùûüç\s\-]+$/u',
                        'message' => 'Veuillez entrer un prénom valide.',
                    ]),
                ],
            ])
            ->add('anniversaire', TypeDateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('genre', ChoiceType::class, [
                'choices'  => [
                    'Masculin' => 'Masculin',
                    'Feminin' => 'Feminin',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('centre_emetteur', EntityType::class, [
                'class' => Associations::class,
                'label' => 'Filtrer par Centre/Association',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC');
                },
            ])
            ->add('region', EntityType::class, [
                'class' => Regions::class,
                'label' => false,
            ])
            ->add('adresse')
            ->add('complement', null, [
                'required' => false
            ])
            ->add('zip')
            ->add('ville')
            ->add('pays')
            ->add('telephone', null, [
                'required' => false
            ])
            ->add('email', null, [
                'required' => false
            ])
            ->add('agree_terms', ChoiceType::class, [
                'choices'  => [
                    'Oui' => '1',
                    'Non' => '0',
                ],
            ])
            ->add('is_active', HiddenType::class,[
                'data' => 1,
            ])
            ->add('impression', ChoiceType::class, [
                'choices'  => [
                    'Oui, imprimez ma licence' => '1',
                    'Non, je souhaite juste le QRCode' => '0',
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateUniqueUser']);

    }

      // Méthode pour la validation personnalisée
      public function validateUniqueUser(FormEvent $event): void
      {
          $form = $event->getForm();
          $data = $form->getData();
  
          // Vérifier uniquement si le formulaire est valide jusqu'à présent
          if (!$form->isValid()) {
              return;
          }
  
          // Vérifier la base de données pour la présence d'un utilisateur similaire
          $userRepository = $this->entityManager->getRepository(Users::class);
  
          $existingUser = $userRepository->findOneBy([
              'nom' => $data->getNom(),
              'prenom' => $data->getPrenom(),
              'anniversaire' => $data->getAnniversaire(),
          ]);
  
          // Si un utilisateur similaire est trouvé, ajouter une violation
          if ($existingUser && $this->isDateWithinOneMonth($data->getAnniversaire(), $existingUser->getAnniversaire())) {
              $form->addError(new FormError('Un utilisateur avec le même nom, prénom et date de naissance existe déjà dans la base de données.'));
          }
      }
  
      // Méthode utilitaire pour vérifier si deux dates sont dans la même tranche d'un mois
      private function isDateWithinOneMonth(\DateTimeInterface $date1, \DateTimeInterface $date2): bool
      {
          $interval = $date1->diff($date2);
  
          // Si la différence est inférieure ou égale à 30 jours
          return $interval->days <= 30;
      }
  

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}

