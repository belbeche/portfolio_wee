<?php

namespace App\Command;

use App\Entity\PushSubscription;
use App\Service\WebPush;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Envoie une notification push aux navigateurs abonnes.
 *
 *   php bin/console app:push:envoyer "Titre" "Le message"
 *   php bin/console app:push:envoyer "Titre" "Le message" --url=/espace
 *   php bin/console app:push:envoyer "Titre" "Le message" --email=client@exemple.fr
 */
class SendPushCommand extends Command
{
    protected static $defaultName = 'app:push:envoyer';
    protected static $defaultDescription = 'Envoie une notification push aux navigateurs abonnes';

    private WebPush $webPush;
    private EntityManagerInterface $em;

    public function __construct(WebPush $webPush, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->webPush = $webPush;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('titre', InputArgument::REQUIRED, 'Le titre de la notification')
            ->addArgument('message', InputArgument::REQUIRED, 'Le corps de la notification')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Page ouverte au clic', '/')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, "Limiter aux abonnements d'un utilisateur");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $criteres = [];
        if (null !== $input->getOption('email')) {
            $criteres['userEmail'] = (string) $input->getOption('email');
        }

        $abonnements = $this->em->getRepository(PushSubscription::class)->findBy($criteres);
        if ([] === $abonnements) {
            $io->warning('Aucun navigateur abonne.');

            return Command::SUCCESS;
        }

        $payload = [
            'title' => (string) $input->getArgument('titre'),
            'body' => (string) $input->getArgument('message'),
            'url' => (string) $input->getOption('url'),
        ];

        $envoyes = 0;
        foreach ($abonnements as $abonnement) {
            $erreur = $this->webPush->send($abonnement, $payload);
            if (null === $erreur) {
                ++$envoyes;
            } else {
                $io->text(sprintf('  echec (%s) : %s', mb_substr($abonnement->getEndpoint(), 0, 48), $erreur));
            }
        }

        $io->success(sprintf('%d notification(s) envoyee(s) sur %d abonnement(s).', $envoyes, count($abonnements)));

        return Command::SUCCESS;
    }
}
