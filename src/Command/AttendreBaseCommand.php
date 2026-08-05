<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Attend que la base de donnees accepte les connexions.
 *
 * Au demarrage du conteneur, PHP et le serveur MySQL partent en meme temps.
 * PHP est pret en une seconde, MySQL met souvent dix a trente secondes a
 * ouvrir son port. Toutes les commandes lancees dans cet intervalle echouent
 * avec « SQLSTATE[HY000] [2002] Connection refused », alors que rien n'est
 * casse : elles sont simplement arrivees trop tot.
 *
 * A placer en tete de run.sh :
 *   php84 bin/console app:base:attendre --timeout=90 || exit 1
 */
class AttendreBaseCommand extends Command
{
    protected static $defaultName = 'app:base:attendre';
    protected static $defaultDescription = 'Attend que la base accepte les connexions avant de continuer';

    private Connection $connexion;

    public function __construct(Connection $connexion)
    {
        parent::__construct();
        $this->connexion = $connexion;
    }

    protected function configure(): void
    {
        $this
            ->addOption('timeout', 't', InputOption::VALUE_REQUIRED, 'Duree maximale d\'attente, en secondes', '60')
            ->addOption('intervalle', 'i', InputOption::VALUE_REQUIRED, 'Secondes entre deux essais', '2');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limite = max(1, (int) $input->getOption('timeout'));
        $intervalle = max(1, (int) $input->getOption('intervalle'));
        $fin = time() + $limite;
        $essai = 0;
        $derniereErreur = '';

        while (time() < $fin) {
            ++$essai;

            try {
                // close() puis SELECT 1 : sans cela, Doctrine garde en memoire
                // la connexion morte du premier essai et rejoue son erreur.
                $this->connexion->close();
                $this->connexion->executeQuery('SELECT 1');

                $io->success(sprintf('Base joignable apres %d essai(s).', $essai));

                return Command::SUCCESS;
            } catch (\Throwable $e) {
                $derniereErreur = $e->getMessage();
                $io->writeln(sprintf(
                    '<comment>Essai %d : base pas encore prete, nouvelle tentative dans %d s...</comment>',
                    $essai,
                    $intervalle
                ));
                sleep($intervalle);
            }
        }

        $io->error(sprintf(
            "La base n'a pas repondu en %d secondes. Derniere erreur : %s",
            $limite,
            $derniereErreur
        ));
        $io->note(
            "Verifie que le service MySQL est demarre, puis que DATABASE_URL pointe bien sur lui. "
            ."Un « Connection refused » signifie que rien n'ecoute sur l'hote et le port indiques : "
            ."ce n'est ni un mot de passe refuse, ni une base absente."
        );

        return Command::FAILURE;
    }
}
