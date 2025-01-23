<?php

// src/Form/CodeBlockType.php
namespace App\Form;

use App\Entity\CodeBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodeBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', TextType::class, [
                'label' => 'Langage',
                'attr' => [
                    'placeholder' => 'Ex: php, javascript, python...',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Code',
                'attr' => [
                    'placeholder' => 'Insérez votre code ici...',
                    'rows' => 10,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CodeBlock::class,
        ]);
    }
}
