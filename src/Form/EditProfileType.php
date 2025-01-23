<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditProfileType extends AbstractType
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
            /*->add('roles', HiddenType::class, [
                    'data' => ['ROLE_USER'],
                ]
            )*/
            // Ajoute le champ d'image avec les options
            ->add('avatar', FileType::class, [
                "row_attr" => [
                    "class" => "d-none"
                ],
                'label' => 'Parcourir',
                'multiple' => false,
                'mapped' => false,
                'required' => false
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Répéter le mot de passe',
                ],
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => false
            ])
            /*->add('favoris')*/
            /*->add('user_roles')*/
            ->add('username', TextType::class, [
                'label' => false,
                'attr' => [
                    'style' => 'display: none;',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
