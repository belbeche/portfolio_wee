<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallbackRequestRelaunchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'disabled' => true, // l'email est affiché mais non modifiable
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message de relance',
                'attr' => [
                    'rows' => 5,
                    'value' => 'Bonjour, nous vous relançons suite à votre demande de rappel. Merci de nous contacter pour plus de détails.',
                ],
            ])
            ->add('attachments', FileType::class, [
                'label' => 'Joindre des fichiers',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
