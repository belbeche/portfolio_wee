<?php

// src/Form/SectionType.php
namespace App\Form;

use App\Entity\Section;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Contenu' => 'content',
                    'Code' => 'code',
                    'Média' => 'media',
                ],
                'label' => 'Type de Section',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu ou Code',
                'required' => false,
                'attr' => ['rows' => 5],
            ]);

        // Dynamique pour type "code" et "media"
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $section = $event->getData();
            $form = $event->getForm();

            if ($section) {
                if ($section->getType() === 'code') {
                    $form->add('language', TextType::class, [
                        'label' => 'Langage',
                        'attr' => ['placeholder' => 'Ex: php, javascript, python'],
                    ]);
                } elseif ($section->getType() === 'media') {
                    $form
                        ->add('mediaType', ChoiceType::class, [
                            'choices' => [
                                'Image' => 'image',
                                'Vidéo' => 'video',
                            ],
                            'label' => 'Type de Média',
                            'required' => true,
                        ])
                        ->add('mediaFile', FileType::class, [
                            'label' => 'Fichier Média (Image ou Vidéo)',
                            'required' => false,
                            'mapped' => false,
                            'constraints' => [
                                new File([
                                    'maxSize' => '5M',
                                    'mimeTypes' => [
                                        'image/jpeg',
                                        'image/png',
                                        'video/mp4',
                                    ],
                                    'mimeTypesMessage' => 'Veuillez envoyer un fichier au format JPEG, PNG ou MP4.',
                                ]),
                            ],
                            'help' => 'Formats acceptés : JPEG, PNG, MP4. Taille maximale : 5 Mo.',
                        ]);
                }
            }
        });

        // Dynamique pour un type défini après soumission
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['type']) && $data['type'] === 'code') {
                $form->add('language', TextType::class, [
                    'label' => 'Langage',
                    'attr' => ['placeholder' => 'Ex: php, javascript, python'],
                ]);
            } elseif (isset($data['type']) && $data['type'] === 'media') {
                $form
                    ->add('mediaType', ChoiceType::class, [
                        'choices' => [
                            'Image' => 'image',
                            'Vidéo' => 'video',
                        ],
                        'label' => 'Type de Média',
                        'required' => true,
                    ])
                    ->add('mediaFile', FileType::class, [
                        'label' => 'Fichier Média (Image ou Vidéo)',
                        'required' => false,
                        'mapped' => false,
                        'constraints' => [
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'video/mp4',
                                ],
                                'mimeTypesMessage' => 'Veuillez envoyer un fichier au format JPEG, PNG ou MP4.',
                            ]),
                        ],
                        'help' => 'Formats acceptés : JPEG, PNG, MP4. Taille maximale : 5 Mo.',
                    ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Section::class,
        ]);
    }
}
