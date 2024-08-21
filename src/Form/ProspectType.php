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
                'label' => 'Upload Document (PDF only)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.'
                    ])
                ],
                'attr' => ['class' => 'form-control']
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