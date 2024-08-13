<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\Message;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\DataTransformer\EmailToUserTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentUser = $options['current_user']; // On suppose que l'utilisateur connecté est passé via les options
        
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
                ],
                'attr' => [
                    'style' => 'display: none;',
                ],
                'label' => false,
            ])
            ->add('devis', EntityType::class, [
                'class' => Devis::class,
                'choice_label' => 'type_de_site_web',
                'placeholder' => 'Choisissez un devis',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($currentUser) {
                    return $er->createQueryBuilder('d')
                              ->where('d.email = :email')
                              ->setParameter('email', $currentUser)
                              ->orderBy('d.created_at', 'DESC'); // Trier par date de création pour voir les plus récents en premier
                },
            ])
            ->add('receiver', ChoiceType::class, [
                'choices' => [
                    'Avancement projet' => 'contact@scriptzenit.fr',
                    'Problème lié à la demande' => 'support@scriptzenit.fr'
                ],
                'placeholder' => 'Choisissez un destinataire',
                'required' => true,
                'label' => 'Service à contacter',
            ])
            ->add('sender', HiddenType::class, [
                'label' => false,
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Joindre un fichier (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Un ou plusieurs fichiers autorisés',
                ],
            ])
            /*->add('receiver')*/
            ;

            // Ajoutez le transformer
            // $builder->get('receiver')
            //         ->addModelTransformer(new EmailToUserTransformer($this->entityManager));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
        $resolver->setRequired('current_user'); // Indique que 'current_user' est requis

    }
    
}
