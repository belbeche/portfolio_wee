<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email'
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('service', ChoiceType::class, [
                'choices' => [
                    'Développement Web Front-End (HTML, CSS, Javascript)' => 'frontend',
                    'Développement Web Back-End (PHP, Node, Django)' => 'backend',
                    'Développement d\'applications mobiles' => 'mobile',
                    'Conception UI/UX' => 'design',
                    'SEO et optimisation' => 'seo',
                    'Maintenance et support' => 'support',
                    'Hébergement et déploiement' => 'hosting',
                    'Autres' => 'other'
                ],
                'label' => 'Objet de la demande',
                'placeholder' => 'Cliquer pour choisir',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
