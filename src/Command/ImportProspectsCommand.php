<?php

namespace App\Command;

use App\Entity\Prospect;
use App\Entity\ProspectNote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe des prospects depuis l'annuaire public des entreprises
 * (recherche-entreprises.api.gouv.fr, donnees ouvertes, sans cle).
 *
 * Exemples :
 *   php bin/console app:prospects:import "agence web" --ville=Rouen
 *   php bin/console app:prospects:import "expert comptable" --ville=Evreux --limit=10
 *   php bin/console app:prospects:import "restaurant" --departement=27 --dry-run
 *
 * Chaque fiche arrive prete a prospecter : entreprise, dirigeant, ville,
 * SIREN, code APE et effectif dans le journal. L'e-mail n'est pas une
 * donnee publique : le champ est rempli avec une adresse a completer.
 */
class ImportProspectsCommand extends Command
{
    protected static $defaultName = 'app:prospects:import';
    protected static $defaultDescription = "Importe des prospects depuis l'annuaire public SIREN";

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('recherche', InputArgument::REQUIRED, 'Termes de recherche, ex : "agence web"')
            ->addOption('ville', null, InputOption::VALUE_REQUIRED, 'Filtrer sur une commune')
            ->addOption('departement', null, InputOption::VALUE_REQUIRED, 'Filtrer sur un departement, ex : 27')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Nombre maximum de fiches a importer', '10')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Afficher sans enregistrer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = max(1, min(25, (int) $input->getOption('limit')));

        $params = [
            'q' => $input->getArgument('recherche'),
            'per_page' => $limit,
            'etat_administratif' => 'A', // uniquement les entreprises actives
        ];
        if ($input->getOption('ville')) {
            $params['q'] .= ' '.$input->getOption('ville');
        }
        if ($input->getOption('departement')) {
            $params['departement'] = $input->getOption('departement');
        }

        $url = 'https://recherche-entreprises.api.gouv.fr/search?'.http_build_query($params);
        $io->text('Interrogation : '.$url);

        $data = $this->fetch($url);
        if (null === $data) {
            $io->error("L'annuaire ne repond pas. Reessayez dans un instant.");

            return Command::FAILURE;
        }

        $results = $data['results'] ?? [];
        if ([] === $results) {
            $io->warning('Aucune entreprise trouvee pour cette recherche.');

            return Command::SUCCESS;
        }

        $repo = $this->em->getRepository(Prospect::class);
        $crees = 0;
        $ignores = 0;

        foreach ($results as $e) {
            $siren = (string) ($e['siren'] ?? '');
            if ('' === $siren) {
                continue;
            }

            $email = sprintf('siren-%s@a-completer.walidbelbeche.fr', $siren);
            if ($repo->findOneBy(['email' => $email])) {
                ++$ignores;
                continue;
            }

            $siege = $e['siege'] ?? [];
            $dirigeant = $e['dirigeants'][0] ?? null;
            $nomDirigeant = $dirigeant
                ? trim(($dirigeant['prenoms'] ?? '').' '.($dirigeant['nom'] ?? ''))
                : null;

            $io->text(sprintf('  + %-40s %-20s %s',
                mb_substr((string) ($e['nom_complet'] ?? ''), 0, 40),
                mb_substr((string) ($siege['libelle_commune'] ?? ''), 0, 20),
                $siren
            ));

            if ($input->getOption('dry-run')) {
                ++$crees;
                continue;
            }

            $prospect = (new Prospect())
                ->setEmail($email)
                ->setCompany((string) ($e['nom_complet'] ?? ''))
                ->setContactName($nomDirigeant)
                ->setCity($siege['libelle_commune'] ?? null)
                ->setPriority('P2')
                ->setSource('Annuaire SIREN, import du '.date('d/m/Y'))
                ->setNotes("E-mail non public : adresse temporaire a remplacer avant tout envoi.");
            $prospect->setServices(['web_development']);

            $fiche = sprintf(
                "[Import annuaire] SIREN %s. APE %s. Effectif : %s. Adresse du siege : %s %s.%s",
                $siren,
                $e['activite_principale'] ?? 'inconnu',
                $e['tranche_effectif_salarie'] ?? 'inconnue',
                $siege['adresse'] ?? '',
                $siege['libelle_commune'] ?? '',
                $nomDirigeant ? ' Dirigeant : '.$nomDirigeant.'.' : ''
            );

            $note = (new ProspectNote())
                ->setProspect($prospect)
                ->setType('note')
                ->setContent($fiche);

            $this->em->persist($prospect);
            $this->em->persist($note);
            ++$crees;
        }

        if (!$input->getOption('dry-run')) {
            $this->em->flush();
        }

        $io->success(sprintf(
            '%d fiche(s) %s, %d deja presente(s).',
            $crees,
            $input->getOption('dry-run') ? 'trouvees (rien enregistre, --dry-run)' : 'importees',
            $ignores
        ));

        return Command::SUCCESS;
    }

    private function fetch(string $url): ?array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'walidbelbeche.fr prospection (contact@walidbelbeche.fr)',
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (false === $body || $status >= 400) {
            return null;
        }

        $data = json_decode((string) $body, true);

        return is_array($data) ? $data : null;
    }
}
