<?php

namespace App\Form;

use App\Entity\Project;
use App\Form\ImageType;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Titre du projet'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Merci de décrire votre projet...',
                ],
            ])
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => true,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Sélections multiples possibles'
                ]
            ])
            ->add(
                'categories',
                EntityType::class,
                [
                    'class'         => Category::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ca')
                            ->orderBy('ca.name', 'DESC');
                    },
                    /*'expanded' => true,*/
                    'multiple'      => true,
                    'choice_label'   => 'name',
                    'expanded' => true,
                    'required'    => false,
                    'attr' => [
                        'class' => 'select2',
                        'placeholder' => 'Choix des catégories'
                    ]
                ]
            )
            ->add(
                'category',
                ChoiceType::class,[
                    'choices' => [
                        'détaillé' => 'détaillé',
                        'lien-direct' => 'lien-direct',
                        'prototype' => 'prototype',
                        'toutes_categories' => 'toutes_categories',
                    ],
                    'attr' => [
                        'class' => 'select2',
                        'placeholder' => 'Choix de la catégorie d\'affichage'
                    ]
                ]
            )
            ->add('link', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Lien fonctionnel du projet'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
