<?php

namespace App\Form;

use App\Entity\Skill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la competence',
                'attr' => ['placeholder' => 'React Native, Symfony, Docker...'],
            ])
            ->add('skillGroup', ChoiceType::class, [
                'label' => 'Famille',
                'choices' => array_flip(Skill::GROUPS),
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Ce que je peux faire avec',
                'help' => "Le libelle choisi ici est celui que le visiteur lit sur l'accueil.",
                'choices' => Skill::levelChoices(),
            ])
            ->add('yearsOfExperience', IntegerType::class, [
                'label' => "Annees de pratique",
                'required' => false,
            ])
            ->add('icon', TextType::class, [
                'label' => 'Fichier icone dans public/img/clients',
                'required' => false,
                'attr' => ['placeholder' => 'spec-4.png'],
            ])
            ->add('description', TextType::class, [
                'label' => 'Precision affichee au survol',
                'required' => false,
            ])
            ->add('position', IntegerType::class, [
                'label' => "Ordre d'affichage",
                'required' => false,
            ])
            ->add('visible', CheckboxType::class, [
                'label' => 'Visible sur le site',
                'required' => false,
            ])
            ->add('highlighted', CheckboxType::class, [
                'label' => "Mettre en avant sur l'accueil",
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Skill::class]);
    }
}
