<?php

namespace App\Form;

use App\Entity\PostSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', HiddenType::class)
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => ['class' => 'section-content', 'rows' => 5],
                'required' => false
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'PHP' => 'php',
                    'JavaScript' => 'javascript',
                    'HTML' => 'html',
                    'CSS' => 'css',
                    'SQL' => 'sql',
                    'Bash' => 'bash',
                    'Plain Text' => 'plaintext'
                ],
                'label' => 'Langage',
                'required' => false,
                'attr' => ['class' => 'language-selector']
            ])
            ->add('mediaFile', FileType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'media-upload']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostSection::class,
        ]);
    }
}