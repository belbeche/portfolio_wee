<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'placeholder' => 'Votre nom'
                ],
                'required' => true
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'placeholder' => 'Votre prénom'
                ],
                'required' => true
            ])
            ->add('civility', ChoiceType::class, [
                'label' => 'Civilité',
                'choices' => [
                    'M' => 'M',
                    'Mme' => 'Mme',
                ],
                'expanded' => true,   // Utilisez ceci si vous souhaitez des boutons radio
                'multiple' => false,  // Gardez ceci sur false pour ne permettre qu'un seul choix,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email de connexion',
                'attr' => [
                    'placeholder' => 'Email de connexion'
                ],
                'required' => true,
            ])
            ->add('roles', HiddenType::class, [
                    'data' => ['ROLE_USER'],
                ]
            )
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répétez le mot de passe'],
                'attr' => [
                    'placeholder' => 'Votre mot de passe'
                ],
            ])
            ->add('checked', CheckboxType::class, [
                'label' => 'Je reconnais avoir lu et compris les CGU et je les accepte',
                'attr' => [
                    'placeholder' => 'Confirmation mdp'
                ],
                'required' => true,
                'mapped' => false,
                'help' => 'En cochant cette case, vous donnez l\'accord pour être contacté par un membre de l\'équipe.',
            ])
        ;

        // Data transformer
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
