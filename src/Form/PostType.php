<?php

// src/Form/PostType.php
namespace App\Form;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\CodeBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr' => ['placeholder' => 'Titre du tutoriel ou cours'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'attr' => ['placeholder' => 'slug-de-l-article'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Introduction (contenu principal)',
                'attr' => [
                    'placeholder' => 'Texte principal du tutoriel',
                    'rows' => 10,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => 'draft',
                    'Validé' => 'validated',
                ],
                'label' => 'Statut de l\'article',
            ])
            ->add('codeBlocks', CollectionType::class, [
                'entry_type' => CodeBlockType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true, // Nécessaire pour les ajouts dynamiques
                'attr' => ['class' => 'code-blocks-collection'],
            ])
            ->add('sections', CollectionType::class, [
                'entry_type' => SectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
            ]);
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
