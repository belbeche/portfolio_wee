<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authorName', TextType::class, [
                'label' => 'Votre nom',
            ])
            ->add('company', TextType::class, [
                'label' => 'Votre structure (facultatif)',
                'required' => false,
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note',
                'choices' => [
                    '5, je recommande sans reserve' => 5,
                    '4, tres satisfait' => 4,
                    '3, correct' => 3,
                    '2, decevant' => 2,
                    '1, a eviter' => 1,
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre experience',
                'attr' => ['rows' => 5],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Review::class]);
    }
}
