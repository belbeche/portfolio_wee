<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Devis;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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
                    'class' => 'devisForm',
                ],
                'expanded' => true,
                'multiple' => false,
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
            ])
            ->add('description_projet', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Merci de décrire votre projet...',
                ]
            ])
            ->add('email', hiddenType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false, // Ne pas mapper ce champ à l'entité Devis,
                'attr' => [
                    'display' => 'none'
                ]
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
        ]);
    }
}
