<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Contenu du message',
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Faible' => 'faible',
                    'Moyenne' => 'moyenne',
                    'Haute' => 'haute',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'en_cours',
                    'En attente' => 'en_attente',
                    'Terminé' => 'termine',
                ],
                'attr' => [
                    'style' => 'display: none;',
                ],
                'label' => false,
            ])
            ->add('devis', HiddenType::class, [
                /*'class' => Devis::class,
                'choice_label' => 'type_de_site_web',
                'placeholder' => 'Choisissez un devis',
                'query_builder' => function (EntityRepository $er) use ($currentUser) {
                    return $er->createQueryBuilder('d')
                        ->where('d.email = :email')
                        ->setParameter('email', $currentUser);
                },*/
                'attr' => [
                    'display' => 'none',
                ],
                'required' => false,
            ])
            ->add('receiverEmail', ChoiceType::class, [
                'choices' => [
                    'Avancement projet' => 'wbelbeche.s@gmail.com',
                    'Problème lié à la demande' => 'wbelbeche.s@gmail.com'
                ],
                'placeholder' => 'Choisissez un destinataire',
                'required' => true,
                'label' => 'Service à contacter',
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Joindre un fichier (optionnel)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
