<?php

namespace App\Form;

use App\Entity\ProspectNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProspectNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'échange',
                'choices' => array_flip(ProspectNote::TYPES),
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => "Ce qui a été dit, ce qui a été décidé, ce qu'il faut retenir pour le prochain contact.",
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ProspectNote::class]);
    }
}
