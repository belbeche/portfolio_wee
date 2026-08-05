<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Met la base au niveau du code, sans risque de perte.
 *
 * A la difference de doctrine:schema:update --complete, cette commande
 * n'execute QUE les ajouts (CREATE TABLE, ADD COLUMN, CREATE INDEX). Tout
 * ce qui supprime ou modifie une colonne existante est affiche mais jamais
 * execute : sur un site en production, une colonne perdue ne se rattrape pas.
 *
 *   php bin/console app:db:maj              -> montre ce qui manque
 *   php bin/console app:db:maj --appliquer  -> applique les ajouts
 */
class UpdateSchemaCommand extends Command
{
    protected static $defaultName = 'app:db:maj';
    protected static $defaultDescription = 'Ajoute a la base les tables et colonnes manquantes, sans rien supprimer';

    private Connection $connexion;

    public function __construct(Connection $connexion)
    {
        parent::__construct();
        $this->connexion = $connexion;
    }

    protected function configure(): void
    {
        $this->addOption('appliquer', null, InputOption::VALUE_NONE, 'Executer reellement les ajouts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $application = $this->getApplication();
        if (null === $application) {
            $io->error('Application console indisponible.');

            return Command::FAILURE;
        }

        // On demande a Doctrine le SQL qu'il executerait, sans l'executer.
        $commande = $application->find('doctrine:schema:update');
        $tampon = new \Symfony\Component\Console\Output\BufferedOutput();
        $commande->run(
            new \Symfony\Component\Console\Input\ArrayInput(['--dump-sql' => true]),
            $tampon
        );

        $lignes = array_filter(array_map('trim', explode("\n", $tampon->fetch())));

        $ajouts = [];
        $ignores = [];
        foreach ($lignes as $ligne) {
            if (!preg_match('/^(CREATE|ALTER|DROP)\s/i', $ligne)) {
                continue;
            }

            // Seuls les ajouts sont surs : tout le reste est signale, jamais joue.
            $estAjout = preg_match('/^CREATE TABLE/i', $ligne)
                || preg_match('/^CREATE (UNIQUE )?INDEX/i', $ligne)
                || (preg_match('/^ALTER TABLE/i', $ligne) && preg_match('/\bADD\b/i', $ligne) && !preg_match('/\bDROP\b/i', $ligne));

            if ($estAjout) {
                $ajouts[] = rtrim($ligne, ';');
            } else {
                $ignores[] = rtrim($ligne, ';');
            }
        }

        if ([] === $ajouts && [] === $ignores) {
            $io->success('La base est deja au niveau du code, rien a faire.');

            return Command::SUCCESS;
        }

        if ([] !== $ajouts) {
            $io->section(sprintf('%d ajout(s) a appliquer', count($ajouts)));
            foreach ($ajouts as $sql) {
                $io->text('  '.$sql);
            }
        }

        if ([] !== $ignores) {
            $io->section(sprintf('%d instruction(s) ignoree(s) volontairement', count($ignores)));
            $io->text('Elles suppriment ou modifient de l\'existant : a examiner a la main.');
            foreach ($ignores as $sql) {
                $io->text('  '.$sql);
            }
        }

        if (!$input->getOption('appliquer')) {
            $io->note('Rien n\'a ete execute. Relance avec --appliquer pour appliquer les ajouts.');

            return Command::SUCCESS;
        }

        $faits = 0;
        foreach ($ajouts as $sql) {
            try {
                $this->connexion->executeStatement($sql);
                ++$faits;
            } catch (\Throwable $e) {
                $io->warning(sprintf('Echec : %s (%s)', $sql, $e->getMessage()));
            }
        }

        $io->success(sprintf('%d ajout(s) applique(s). Pense a vider le cache du serveur.', $faits));

        return Command::SUCCESS;
    }
}
