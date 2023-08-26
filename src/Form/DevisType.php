<?php

namespace App\Form;

use App\Entity\Devis;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DevisType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_de_site_web', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Présentation entreprise' => 'site_vitrine',
                    'Site marchand' => 'site_e-commerce',
                    'Application web et mobile' => 'application_cross_plateforme',
                    'Application spécifique' => 'Autre'
                ],
                'attr' => [
                    'class' => 'devisForm selectpicker',
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => $options['default_type_de_site_web'], // Utilisez l'option passée depuis le contrôleur
            ])
            ->add('attentes_design_web', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Un design standard' => 'design_standard',
                    'Vous avez vos propres maquettes' => 'own_wireframe',
                    'Avancée' => 'advanced'
                ],
                'placeholder' => 'Cliquer pour choisir',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ]
            ])
            ->add('description_projet', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Merci de décrire votre projet...',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'required' => true,
                'mapped' => true,
            ])
            // Ajouter le champ "status" sans le mapper avec l'entité
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Validé' => 'validé',
                    'En attente' => 'en_attente',
                    'Brouillon' => 'brouillon',
                ],
                'attr' => [
                    'style' => 'display: none;',
                ],
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
            'default_type_de_site_web' => null,
        ]);
    }
}
