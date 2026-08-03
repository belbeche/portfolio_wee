<?php

namespace App\Command;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Affecte un secteur aux realisations qui n'en ont pas encore.
 *
 * Le parcours par secteur de l'accueil (?secteur=Association...) filtre sur
 * ce champ : tant qu'il est vide, les portes de l'accueil menent a des pages
 * vides. Cette commande classe les projets connus par mots-cles du titre et
 * ne touche jamais un secteur deja renseigne (idempotente).
 *
 *   php bin/console app:projets:classer
 */
class ClassifyProjectsCommand extends Command
{
    protected static $defaultName = 'app:projets:classer';
    protected static $defaultDescription = "Affecte un secteur aux realisations qui n'en ont pas";

    /** Mot-cle du titre (en minuscules) => secteur du parcours d'accueil. */
    private const MAP = [
        'axishumain' => 'Startup',
        'reves' => 'Startup',
        'rêves' => 'Startup',
        'historia' => 'Startup',
        'suspension' => 'Startup',
        'vocasant' => 'Association',
        'monsuiviaddiction' => 'Association',
        'sunday' => 'Commerce',
        'somla' => 'Commerce',
        'plomberie' => 'Commerce',
        'sicadan' => 'Entreprise',
        'stock' => 'Entreprise',
        'concurrence' => 'Entreprise',
        'avallon' => 'Entreprise',
        'agence' => 'Entreprise',
    ];

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projects = $this->em->getRepository(Project::class)->findAll();

        $classes = 0;
        $restants = [];

        foreach ($projects as $project) {
            if ('' !== trim((string) $project->getSector())) {
                continue; // deja classe : on ne touche pas
            }

            $titre = mb_strtolower((string) $project->getTitle());
            $trouve = null;
            foreach (self::MAP as $motCle => $secteur) {
                if (false !== mb_strpos($titre, $motCle)) {
                    $trouve = $secteur;
                    break;
                }
            }

            if (null !== $trouve) {
                $project->setSector($trouve);
                $io->text(sprintf('  %-45s -> %s', mb_substr((string) $project->getTitle(), 0, 45), $trouve));
                ++$classes;
            } else {
                $restants[] = (string) $project->getTitle();
            }
        }

        if ($classes > 0) {
            $this->em->flush();
        }

        $io->success(sprintf('%d realisation(s) classee(s).', $classes));
        if ([] !== $restants) {
            $io->note("A classer a la main depuis le back office :\n  - ".implode("\n  - ", $restants));
        }

        return Command::SUCCESS;
    }
}
