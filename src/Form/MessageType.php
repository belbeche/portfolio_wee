<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\Message;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $devisList = $options['devisList']; // Récupérer les devis transmis
        

        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Contenu du message',
                'attr' => [
                    'placeholder' => 'Votre demande en quelques mots...',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Faible' => 'faible',
                    'Moyenne' => 'moyenne',
                    'Haute' => 'haute',
                    'Urgente' => 'urgente'
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'en_cours',
                    'En attente' => 'en_attente',
                    'Terminé' => 'termine',
                    'Livré' => 'livre',
                ],
                'attr' => [
                    'style' => 'display: none;',
                ],
                'label' => false,
            ])
            ->add('devis', ChoiceType::class, [
                'choices' => $devisList, // Passer la liste des devis à ce champ
                'choice_label' => function (Devis $devis) {
                    return $devis->getTypeDeSiteWeb();
                },
                'placeholder' => 'Choisissez un devis',
                'required' => true,
            ])
            ->add('receiver', ChoiceType::class, [
                'choices' => [
                    'Avancement projet' => 'contact@scriptzenit.fr',
                    'Problème lié à la demande' => 'support@scriptzenit.fr',
                    'Autre demandes' => 'w.belbeche@scriptzenit.fr',
                ],
                'placeholder' => 'Choisissez un destinataire',
                'required' => true,
                'label' => 'Service à contacter',
            ])
            ->add('sender', HiddenType::class, [
                'mapped' => false, // Non mappé à l'entité
                'data' => $options['current_user']->getId(), // ID de l'utilisateur connecté
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Joindre un fichier (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Un ou plusieurs fichiers autorisés',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);

        $resolver->setRequired(['current_user', 'devisList']); // `devisList` est requis
    }
}
