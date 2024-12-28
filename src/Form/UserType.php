<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints as Assert;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Par défaut, on affiche le champ de mot de passe
        $passwordFieldOptions = [
            'type' => PasswordType::class,
            'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => false,
            'mapped' => true,
            'first_options' => ['label' => 'Votre mot de passe'],
            'second_options' => ['label' => 'Re-petez le mot de passe'],
            'attr' => [
                'placeholder' => 'Votre mot de passe'
            ],
        ];

        // Vérifie si la route est '/inscription' pour cacher le champ de mot de passe
        $request = Request::createFromGlobals();
        if ($request->getPathInfo() === '/inscription') {
            $passwordFieldOptions['type'] = HiddenType::class; // Change le type du champ de mot de passe en hidden
        }
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('roles', HiddenType::class, [
                    'data' => ['ROLE_USER'],
                ]
            )
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control ',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'label' => false
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control ',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'label' => false
            ])
            ->add('civility', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'M.' => 'M',
                    'Mme.' => 'Mme',
                ],
                'expanded' => true,   // Liste déroulante
                'multiple' => false,  // Sélection multiple
            ])
            ->add('password', RepeatedType::class, $passwordFieldOptions)
            ->add('checked', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label' => 'Je reconnais avoir lu et compris les CGU ainsi que les CGUV et je les accepte',
                'required' => true,
                'mapped' => false
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
