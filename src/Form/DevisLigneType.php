<?php

namespace App\Form;

use App\Entity\DevisLigne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'attr' => ['placeholder' => 'Intégration du site vitrine'],
            ])
            ->add('detail', TextareaType::class, [
                'label' => 'Détail',
                'required' => false,
                'attr' => [
                    'rows' => 2,
                    'placeholder' => "Ce que la ligne comprend précisément. C'est ce qui évite les malentendus trois semaines plus tard.",
                ],
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Qté',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => '0.25', 'min' => '0', 'class' => 'js-ligne'],
            ])
            ->add('unite', ChoiceType::class, [
                'label' => 'Unité',
                'choices' => array_flip(DevisLigne::UNITES),
            ])
            ->add('prixUnitaire', NumberType::class, [
                'label' => 'P.U. HT (€)',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => '0.01', 'min' => '0', 'class' => 'js-ligne'],
            ])
            ->add('remise', NumberType::class, [
                'label' => 'Remise (%)',
                'scale' => 2,
                'required' => false,
                'html5' => true,
                'attr' => ['step' => '1', 'min' => '0', 'max' => '100', 'class' => 'js-ligne'],
            ])
            ->add('tauxTva', NumberType::class, [
                'label' => 'TVA (%)',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => '0.1', 'min' => '0', 'class' => 'js-ligne'],
            ])
            ->add('position', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DevisLigne::class]);
    }
}
