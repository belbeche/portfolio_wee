<?php

namespace App\Command;

use App\Service\Sauvegarde;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Sauvegarde la base et les fichiers envoyes dans une archive datee.
 *
 * Utilisation : php bin/console app:sauvegarde
 */
class SauvegardeCommand extends Command
{
    protected static $defaultName = 'app:sauvegarde';
    protected static $defaultDescription = 'Cree une archive de la base et des fichiers envoyes';

    private Sauvegarde $sauvegarde;

    public function __construct(Sauvegarde $sauvegarde)
    {
        parent::__construct();
        $this->sauvegarde = $sauvegarde;
    }

    protected function configure(): void
    {
        $this
            ->addOption('lister', null, InputOption::VALUE_NONE, 'Affiche les archives existantes sans en creer une nouvelle')
            ->addOption('si-necessaire', null, InputOption::VALUE_NONE, 'Ne sauvegarde que si la derniere date de plus de 20 h');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('lister')) {
            $archives = $this->sauvegarde->archives();
            if ([] === $archives) {
                $io->warning('Aucune sauvegarde pour le moment.');

                return Command::SUCCESS;
            }

            $io->table(
                ['Archive', 'Taille', 'Date'],
                array_map(static fn ($a) => [
                    $a['nom'],
                    sprintf('%.1f Mo', $a['octets'] / 1048576),
                    $a['date']->format('d/m/Y H:i'),
                ], $archives)
            );

            return Command::SUCCESS;
        }

        // Utilise au demarrage du conteneur : sans ce garde-fou, trois
        // redemarrages dans la journee font trois archives pour rien.
        if ($input->getOption('si-necessaire')) {
            $age = $this->sauvegarde->ageDerniereEnHeures();
            if (null !== $age && $age < 20) {
                $io->text(sprintf('Derniere sauvegarde il y a %s h : rien a faire.', $age));

                return Command::SUCCESS;
            }
        }

        try {
            $debut = microtime(true);
            $chemin = $this->sauvegarde->creer();
            $secondes = round(microtime(true) - $debut, 1);

            $io->success(sprintf(
                '%s cree en %s s (%.1f Mo). %d archive(s) conservee(s).',
                basename($chemin),
                $secondes,
                filesize($chemin) / 1048576,
                count($this->sauvegarde->archives())
            ));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Sauvegarde impossible : '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
