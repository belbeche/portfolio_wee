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


// the "name" and "description" arguments of AsCommand replace the
// static $defaultName and $defaultDescription properties


class CreateAdministratorCommand extends Command
{

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-admin';
    // the command description shown when running "php bin/console list"
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

    /**
     * Definition des argument a entrer dans la commande
     */
    protected function configure(): void
    {
        $this
            ->addArgument('nom', InputArgument::OPTIONAL, 'nom')
            ->addArgument('prenom', InputArgument::OPTIONAL, 'Le prenom de l\'utilisateur')
            ->addArgument('email', InputArgument::OPTIONAL, 'L\'email de l\'utilisateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Le mot de passe de l\'utilisateur en clair');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);


        //* ========================================================
        //* ========================================================

        //? >> Interaction, question et recuperation de la reponse

        //* ========================================================
        //todo Ajouter des contrainte sur la saisi du mot de passe

        $nom = $input->getArgument('nom');
        if (!$nom) {
            $question = new Question('Quel est le nom de l\'administrateur :  ');
            $nom = $helper->ask($input, $output, $question);
        }

        $prenom = $input->getArgument('prenom');
        if (!$prenom) {
            $question = new Question('Quel est le prenom de l\'administrateur :  ');
            $prenom = $helper->ask($input, $output, $question);
        }

        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Quel est l\'email de l\'administrateur :  ');
            $email = $helper->ask($input, $output, $question);
        }

        $plainPassword = $input->getArgument('password');
        if (!$plainPassword) {
            $question = new Question('Quel est le mot de passe de l\'administrateur :  ');
            $plainPassword = $helper->ask($input, $output, $question);
        }

        // Enregistrement en BDD

        $user = (new User());
        $user
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setEmail($email)
            ->setPassword($this->hasher->hashpassword(
                $user,
                $plainPassword
            ))
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setIsVerified('1');

        $this->userRepository->save($user, true);



        $io->success('Nouvelle Administrateur créer !');

        return Command::SUCCESS;
    }
}