<?php

namespace App\Form;

use App\Entity\Prospect;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('services', ChoiceType::class, [
                'label' => 'Select Services',
                'choices' => [
                    'Web Development' => 'web_development',
                    'Custom Software Development' => 'custom_software',
                    'IT Consulting' => 'it_consulting',
                    'Cloud Solutions' => 'cloud_solutions',
                    'Cybersecurity Services' => 'cybersecurity',
                    'Data Analytics' => 'data_analytics',
                    'AI & Machine Learning' => 'ai_ml',
                    'IoT Solutions' => 'iot_solutions',
                    'Blockchain Development' => 'blockchain',
                    'Mobile App Development' => 'mobile_app',
                    'Digital Marketing' => 'digital_marketing',
                    'UI/UX Design' => 'ui_ux_design',
                    'Network Management' => 'network_management',
                    'Technical Support Services' => 'tech_support'
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Prospect::class,
        ]);
    }
}