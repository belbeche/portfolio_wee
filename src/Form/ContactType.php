<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'contact_form.first_name_label', // Translation key
                'required' => false,
                'attr' => [
                    'placeholder' => 'contact_form.first_name_placeholder', // Translation key
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'contact_form.last_name_label', // Translation key
                'required' => false,
                'attr' => [
                    'placeholder' => 'contact_form.last_name_placeholder', // Translation key
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'contact_form.email_label', // Translation key
                'required' => false,
                'attr' => [
                    'placeholder' => 'contact_form.email_placeholder', // Translation key
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'contact_form.phone_label', // Translation key
                'required' => false,
                'attr' => [
                    'placeholder' => 'contact_form.phone_placeholder', // Translation key
                ],
            ])
            ->add('service', ChoiceType::class, [
                'choices' => [
                    'contact_form.service.frontend' => 'frontend',
                    'contact_form.service.backend' => 'backend',
                    'contact_form.service.mobile' => 'mobile',
                    'contact_form.service.design' => 'design',
                    'contact_form.service.seo' => 'seo',
                    'contact_form.service.support' => 'support',
                    'contact_form.service.hosting' => 'hosting',
                    'contact_form.service.other' => 'other',
                ],
                'label' => 'contact_form.service_label', // Translation key
                'placeholder' => 'contact_form.service_placeholder', // Translation key
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'contact_form.message_label', // Translation key
                'required' => false,
                'attr' => [
                    'placeholder' => 'contact_form.message_placeholder', // Translation key
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
