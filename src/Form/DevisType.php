<?php

namespace App\Form;

use App\Entity\Devis;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                    'Site vitrine (Présentation de l\'entreprise)' => 'site_vitrine',
                    'Réalisation de site d\'annonce immobilière' => 'site_real_estate',
                    'E-Commerce (Vente en ligne)' => 'site_e-commerce',
                    'Blog professionnel' => 'site_blog',
                    'Portfolio (pour artistes, designers, etc.)' => 'site_portfolio',
                    'Forum communautaire' => 'site_forum',
                    'Site d\'information ou magazine en ligne' => 'site_info_magazine',
                    'Application Web et Mobile (Cross-plateforme)' => 'application_cross_plateforme',
                    'Système de Gestion de Contenu (CMS)' => 'systeme_gestion_contenu',
                    'Intranet d\'entreprise' => 'intranet',
                    'Application spécifique (Décrivez vos besoins)' => 'autre'
                ],
                'attr' => [
                    'class' => 'devisForm selectpicker',
                ],
                'multiple' => false,
                'data' => $options['default_type_de_site_web'], // Utilisez l'option passée depuis le contrôleur
            ])
            ->add('attentes_design_web', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Design standard' => 'design_standard',
                    'Avec vos propres maquettes' => 'own_wireframe',
                    'Design avancé' => 'advanced',
                    'Design orienté vers le mobile' => 'mobile_focused',
                    'Design animé ou interactif' => 'animated_interactive',
                    'Thème sombre' => 'dark_theme',
                    'Thème clair' => 'light_theme',
                    'Design axé sur l’accessibilité' => 'accessibility_focused',
                    'Design minimaliste' => 'minimalist',
                    'Branding complet inclus' => 'full_branding',
                    'Personnalisation avancée' => 'high_customization',
                ],
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
                'required' => false,
                'mapped' => true,
                'attr' => [
                    'placeholder' => 'Votre adresse email',
                ],
            ])
            // Ajouter le champ "status" sans le mapper avec l'entité
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Livré' => 'Livré',
                    'En cours' => 'En_cours',
                    'En attente' => 'en_attente',
                    'Retardé' => 'Retardé',
                    'Annulé' => 'Annulé'
                ],
                'attr' => [
                    'style' => 'display: none;',
                ],
                'label' => false,
            ])
            ->add('prix', NumberType::class, [
                'label' => false,
                'attr' => [
                    'style' => 'display: none;',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
            'default_type_de_site_web' => null,
        ]);
    }
}
