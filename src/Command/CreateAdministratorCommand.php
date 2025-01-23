<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdministratorCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    protected static $defaultDescription = 'Create a new Admin user.';

    private UserPasswordHasherInterface $hasher;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $hasher, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nom', InputArgument::OPTIONAL, 'Le nom de l\'utilisateur')
            ->addArgument('prenom', InputArgument::OPTIONAL, 'Le prénom de l\'utilisateur')
            ->addArgument('email', InputArgument::OPTIONAL, 'L\'email de l\'utilisateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Le mot de passe de l\'utilisateur en clair');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        $nom = $input->getArgument('nom');
        if (!$nom) {
            $question = new Question('Quel est le nom de l\'administrateur :  ');
            $nom = $helper->ask($input, $output, $question);
        }

        $prenom = $input->getArgument('prenom');
        if (!$prenom) {
            $question = new Question('Quel est le prénom de l\'administrateur :  ');
            $prenom = $helper->ask($input, $output, $question);
        }

        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Quel est l\'email de l\'administrateur :  ');
            $email = $helper->ask($input, $output, $question);
        }

        // Vérification que l'email n'existe pas déjà
        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        $plainPassword = $input->getArgument('password');
        if (!$plainPassword) {
            $question = new Question('Quel est le mot de passe de l\'administrateur :  ');
            $plainPassword = $helper->ask($input, $output, $question);
        }

        // Création et enregistrement de l'utilisateur
        $user = new User();
        $user
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setEmail($email)
            ->setUser($user)
            ->setPassword($this->hasher->hashPassword(
                $user,
                $plainPassword
            ))
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setIsVerified(true); // Utiliser un booléen ici

        $this->userRepository->save($user, true);

        $io->success('Nouvel administrateur créé !');

        return Command::SUCCESS;
    }
}
