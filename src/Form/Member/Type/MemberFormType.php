<?php

namespace App\Form\Member\Type;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom du membre',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom du membre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du membre',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email du membre'
            ])
            ->add('function', TextType::class, [
                'label' => 'Fonction du membre'
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Salarie' => 'salarie',
                    'Ancien Stagiaire' => 'ancien_stagiaire',
                    'Contributeur/Partenaire' => 'contributeur_partenaire',
                ],
                'label' => 'Catégorie',
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (Image file)',
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
