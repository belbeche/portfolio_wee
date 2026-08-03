<?php

namespace App\Command;

use App\Entity\Prospect;
use App\Repository\ProspectRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Le point de prospection quotidien, en une commande.
 *
 *   php bin/console app:prospection:daily
 *
 * Affiche, dans l'ordre où il faut les traiter :
 *   1. les relances dues aujourd'hui  (le plus rentable : 60 à 70 % des
 *      réponses arrivent après la première relance) ;
 *   2. les prospects contactés puis oubliés, sans relance programmée ;
 *   3. les 10 prochaines cibles à contacter ;
 *   4. l'état du tunnel et le diagnostic associé.
 */
#[AsCommand(
    name: 'app:prospection:daily',
    description: 'Point de prospection : relances dues, cibles du jour, état du tunnel'
)]
class ProspectionDailyCommand extends Command
{

    private ProspectRepository $prospects;

    public function __construct(ProspectRepository $prospects)
    {
        parent::__construct();
        $this->prospects = $prospects;
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Nombre de cibles à contacter à afficher', '10');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = max(1, (int) $input->getOption('limit'));

        $io->title('Prospection du '.(new \DateTime())->format('d/m/Y'));

        // --- 1. Relances dues -------------------------------------------
        $due = $this->prospects->findDueForFollowUp();

        if ($due) {
            $io->section(sprintf('%d relance(s) due(s), à traiter en premier', count($due)));
            $io->table(
                ['Société', 'Contact', 'Email', 'Relance n°', 'Prévue le'],
                array_map(static fn (Prospect $p) => [
                    $p->getCompany() ?: '-',
                    $p->getContactName() ?: '-',
                    $p->getEmail(),
                    $p->getFollowUpCount() + 1,
                    $p->getNextFollowUpAt() ? $p->getNextFollowUpAt()->format('d/m/Y') : '-',
                ], $due)
            );
        } else {
            $io->success('Aucune relance en retard.');
        }

        // --- 2. Prospects orphelins -------------------------------------
        $orphans = $this->prospects->findOrphans();

        if ($orphans) {
            $io->section(sprintf('%d prospect(s) contacté(s) sans relance programmée', count($orphans)));
            $io->warning(
                "Ce sont des prospects perdus par inadvertance. Programme une relance "
                ."ou passe-les en « perdu », mais ne les laisse pas dans cet état."
            );
            $io->listing(array_map(
                static fn (Prospect $p) => sprintf(
                    '%s (%s), dernier contact le %s',
                    $p->getCompany() ?: $p->getEmail(),
                    $p->getEmail(),
                    $p->getLastContactedAt() ? $p->getLastContactedAt()->format('d/m/Y') : '?'
                ),
                array_slice($orphans, 0, 15)
            ));
        }

        // --- 3. Cibles du jour ------------------------------------------
        $next = $this->prospects->findNextToContact($limit);

        if ($next) {
            $io->section(sprintf('%d cible(s) à contacter', count($next)));
            $io->table(
                ['Priorité', 'Société', 'Ville', 'Contact', 'Email', 'Site'],
                array_map(static fn (Prospect $p) => [
                    $p->getPriority() ?: '-',
                    $p->getCompany() ?: '-',
                    $p->getCity() ?: '-',
                    $p->getContactName() ?: '-',
                    $p->getEmail(),
                    $p->getWebsite() ?: '-',
                ], $next)
            );
        } else {
            $io->note('Plus aucune cible « à contacter » en base. Il est temps de recharger la liste.');
        }

        // --- 4. État du tunnel ------------------------------------------
        $counts = $this->prospects->countByStatus();
        $rate = $this->prospects->computeResponseRate();

        $io->section('État du tunnel');
        $io->table(
            ['Statut', 'Nombre'],
            array_map(
                static fn (string $key, string $label) => [$label, $counts[$key] ?? 0],
                array_keys(Prospect::STATUSES),
                array_values(Prospect::STATUSES)
            )
        );
        $io->writeln(sprintf('  Taux de réponse : <info>%s %%</info>', $rate));

        // --- Diagnostic --------------------------------------------------
        $contacted = $counts[Prospect::STATUS_CONTACTED] + $counts[Prospect::STATUS_FOLLOWED_UP];

        if ($contacted >= 20 && $rate < 10.0) {
            $io->warning(
                "Taux de réponse sous 10 % sur un volume significatif : le problème est le "
                ."MESSAGE ou le CIBLAGE, pas le volume. Réécris l'accroche avant d'envoyer plus."
            );
        } elseif ($rate >= 10.0 && 0 === $counts[Prospect::STATUS_PROPOSAL] && $counts[Prospect::STATUS_MEETING] >= 3) {
            $io->warning(
                'Des rendez-vous mais aucune proposition envoyée : le blocage est en sortie '
                ."d'appel. Termine chaque échange par une estimation sous 48 h."
            );
        }

        return Command::SUCCESS;
    }
}
