<?php

namespace App\Command;

use App\Service\Surveillance;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Passe les controles de surveillance et previent en cas de changement.
 *
 * Utilisation : php bin/console app:surveillance
 *               php bin/console app:surveillance --forcer   (envoie toujours)
 */
class SurveillanceCommand extends Command
{
    protected static $defaultName = 'app:surveillance';
    protected static $defaultDescription = 'Controle la base, le disque, la sauvegarde, l\'hebergement et les envois';

    private Surveillance $surveillance;

    public function __construct(Surveillance $surveillance)
    {
        parent::__construct();
        $this->surveillance = $surveillance;
    }

    protected function configure(): void
    {
        $this->addOption('forcer', null, InputOption::VALUE_NONE, 'Envoie le rapport meme si rien n\'a change');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $bilan = $this->surveillance->surveiller((bool) $input->getOption('forcer'));

        $io->table(
            ['Controle', 'Etat', 'Detail'],
            array_map(static fn ($c) => [$c['libelle'], $c['ok'] ? 'OK' : 'ALERTE', $c['detail']], $bilan['controles'])
        );

        $enPanne = array_filter($bilan['controles'], static fn ($c) => !$c['ok']);

        if ($bilan['alerte']) {
            $io->warning('Notification envoyee : '.$bilan['message']);
        } else {
            $io->text('Aucun changement depuis le dernier passage : personne n\'est derange.');
        }

        return [] === $enPanne ? Command::SUCCESS : Command::FAILURE;
    }
}
