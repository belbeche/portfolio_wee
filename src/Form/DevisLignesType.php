<?php

namespace App\Form;

use App\Entity\Devis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Le decompte detaille d'un devis.
 *
 * allow_delete + by_reference a false : sans ces deux options, retirer une
 * ligne dans le navigateur ne la supprime pas en base, elle reapparait au
 * rechargement. C'est le piege classique des collections Symfony.
 */
class DevisLignesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('lignes', CollectionType::class, [
            'entry_type' => DevisLigneType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => false,
            'prototype_name' => '__ligne__',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Devis::class]);
    }
}
