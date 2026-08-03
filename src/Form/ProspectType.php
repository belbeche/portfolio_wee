<?php

namespace App\Form;

use App\Entity\Prospect;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ProspectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class, [
                'label' => 'Entreprise',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('contactName', TextType::class, [
                'label' => 'Contact',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('website', TextType::class, [
                'label' => 'Site web',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('services', ChoiceType::class, [
                'label' => 'Prestations visées',
                'choices' => [
                    'Application mobile React Native' => 'mobile_app',
                    'Site ou application web' => 'web_development',
                    'Logiciel métier sur mesure' => 'custom_software',
                    'API et intégrations' => 'it_consulting',
                    'Hébergement et supervision' => 'cloud_solutions',
                    'Sécurité applicative' => 'cybersecurity',
                    'Interlocuteur Unique (retainer)' => 'tech_support',
                ],
                'expanded' => true,
                'multiple' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('document', FileType::class, [
                'label' => 'Documents (PDF, images, etc.)',
                'multiple' => true,  // Permet l'upload de plusieurs fichiers
                'mapped' => false,   // Ne lie pas directement à l'entité
                'constraints' => [                
                    new All(
                        new File([
                            'maxSize' => '5000k',
                            'mimeTypes' => [
                                'application/pdf',
                                'image/jpeg',
                                'image/png'
                            ],
                            'mimeTypesMessage' => 'Merci d\'upload un fichier pdf, jpeg ou png',
                        ])
                    )
                ],
            ])
            ->add('notes', null, [
                'label' => 'Notes',
                'attr' => ['class' => 'form-control']
            ])
            ->add('message', TextareaType::class, [
                'label' => "Message personnalisé pour l'e-mail",
                'help' => "Facultatif. S'il est rempli, ce texte remplace le paragraphe générique de l'e-mail envoyé au prospect, et il est archivé dans son journal.",
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5,
                           'placeholder' => "Bonjour, suite à notre échange de ce matin concernant votre application de réservation..."],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Prospect::class,
        ]);
    }
}