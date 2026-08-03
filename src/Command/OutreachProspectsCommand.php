<?php

namespace App\Command;

use App\Entity\Prospect;
use App\Service\ProspectOutreach;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Prospection automatisee, en deux temps.
 *
 *   Premier contact (statut "A contacter", e-mail verifie) :
 *     php bin/console app:prospects:contacter            -> simulation
 *     php bin/console app:prospects:contacter --envoyer  -> envoi reel
 *
 *   Relances dues (cadence J+4 puis J+10, jamais plus de deux) :
 *     php bin/console app:prospects:contacter --relances --envoyer
 *
 * La logique d'envoi (gabarits, garde-fous, journal, cadence) vit dans
 * App\Service\ProspectOutreach : c'est la meme que celle des boutons
 * du back office, page Prospects.
 */
class OutreachProspectsCommand extends Command
{
    protected static $defaultName = 'app:prospects:contacter';
    protected static $defaultDescription = 'Envoie les premiers contacts et les relances dues, avec garde-fous';

    private ProspectOutreach $outreach;
    private EntityManagerInterface $em;

    public function __construct(ProspectOutreach $outreach, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->outreach = $outreach;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addOption('envoyer', null, InputOption::VALUE_NONE, "Envoyer reellement. Sans cette option : simulation.")
            ->addOption('relances', null, InputOption::VALUE_NONE, 'Traiter les relances dues au lieu des premiers contacts')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Plafond de cette execution', (string) ProspectOutreach::MAX_PAR_VAGUE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reel = (bool) $input->getOption('envoyer');
        $relances = (bool) $input->getOption('relances');
        $limit = max(1, min(ProspectOutreach::MAX_PAR_VAGUE, (int) $input->getOption('limit')));

        $candidats = $relances
            ? $this->outreach->dueFollowUps()
            : $this->outreach->firstContactCandidates();

        if ([] === $candidats) {
            $io->success($relances ? 'Aucune relance due.' : 'Aucun prospect a contacter avec un e-mail verifie.');

            return Command::SUCCESS;
        }

        $candidats = array_slice($candidats, 0, $limit);
        $io->section(sprintf('%s : %d envoi(s) %s',
            $relances ? 'Relances' : 'Premiers contacts',
            count($candidats),
            $reel ? 'REELS' : 'simules (ajoute --envoyer pour envoyer)'
        ));

        foreach ($candidats as $prospect) {
            $io->text(sprintf('  -> %-34s %-24s %s',
                mb_substr((string) $prospect->getCompany(), 0, 34),
                mb_substr((string) $prospect->getCity(), 0, 24),
                $prospect->getEmail()
            ));
        }

        if (!$reel) {
            $io->note('Simulation terminee. Relance la commande avec --envoyer pour envoyer reellement.');

            return Command::SUCCESS;
        }

        $resultat = $this->outreach->sendWave($candidats, $relances, $limit);

        foreach ($resultat['errors'] as $societe => $message) {
            $io->warning(sprintf('  Echec pour %s : %s', $societe, $message));
        }

        $io->success(sprintf('%d e-mail(s) envoyes. Cadence de relance posee automatiquement (J+4 puis J+10).', $resultat['sent']));

        return Command::SUCCESS;
    }
}
