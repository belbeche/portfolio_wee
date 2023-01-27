<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Devis;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_de_site_web', ChoiceType::class, [
                'label' => 'Type de site web',
                'choices' => [
                    'Site vitrine' => 'site_vitrine',
                    'Site e-commerce' => 'site_e-commerce',
                    'Application web' => 'application_web',
                    'Autre' => 'Autre'
                ],
                'attr' => [
                    'class' => 'devisForm',
                ],
                'multiple' => false,
            ])
            ->add('attentes_design_web', ChoiceType::class, [
                'label' => 'Quelles sont vos attentes concernant le design du site ?',
                'choices' => [
                    'Un design standard' => 'design_standard',
                    'Vous avez vos propres maquettes' => 'own_wireframe',
                    'Avancée' => 'advanced'
                ]
            ])
            ->add('description_projet', TextareaType::class, [])
            ->add('multi_langues', CheckboxType::class, [
                'label' => 'Multi languages ?',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
