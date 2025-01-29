<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;  // Ajouté pour les types Text
use Symfony\Component\Form\Extension\Core\Type\DateType;  // Ajouté pour les dates
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('deadline', null, [
                'widget' => 'single_text',
                'label' => 'Date limite',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Professionnel' => 'Professionnel',
                    'Personnel' => 'Personnel',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Non commencé' => 'Non commencé',
                    'En cours' => 'En cours',
                    'Terminé' => 'Terminé',
                ],
                'placeholder' => 'Sélectionner un statut',
                'required' => false,
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Très basse' => 1,
                    'Basse' => 2,
                    'Moyenne' => 3,
                    'Haute' => 4,
                    'Très haute' => 5,
                ],
                'placeholder' => 'Sélectionner une priorité',
                'required' => false,
            ])
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
