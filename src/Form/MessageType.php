<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\Message;
use App\Form\DataTransformer\EmailToUserTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class MessageType extends AbstractType
{
    private $security;
    private $entityManager;

    public function __construct(Security $security,EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Le formulaire reçoit l'utilisateur actuel via les options
        $currentUser = $options['attr']['current_user'];

        // Construire la requête pour récupérer les devis associés à l'utilisateur actuel
        $devisQueryBuilder = function (EntityRepository $er) use ($currentUser) {
            return $er->createQueryBuilder('d')
                ->where('d.user = :user')
                ->setParameter('user', $currentUser);
        };

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
                'query_builder' => function (EntityRepository $er) use ($currentUser) {
                    return $er->createQueryBuilder('d')
                        ->where('d.email = :email')
                        ->setParameter('email', $currentUser);
                },
                'required' => true,
            ])
            ->add('sender', ChoiceType::class, [
                'choices' => [
                    'Avancement projet' => 'contact@scriptzenit.fr',
                    'Problème lié à la demande' => 'support@scriptzenit.fr'
                ],
                'placeholder' => 'Choisissez un destinataire',
                'required' => true,
                'label' => 'Service à contacter',
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Joindre un fichier (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Un ou plusieurs fichiers autorisés',
                ],
            ])
            ->add('receiver')
            ;

            // Ajoutez le transformer
            $builder->get('receiver')
                ->addModelTransformer(new EmailToUserTransformer($this->entityManager));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
