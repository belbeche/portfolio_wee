<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Diagnostic de schéma.
 *
 *   php bin/console app:diag:schema
 *
 * L'erreur « The table with name X already exists » remonte de
 * Doctrine\DBAL\Schema\Schema::_addTable(). Elle signifie qu'au moment de
 * construire le schéma en mémoire, deux ClassMetadata différentes réclament
 * la même table. Cette commande les nomme, au lieu de laisser deviner.
 *
 * Elle affiche aussi les tables réellement présentes en base, pour distinguer
 * un problème de mapping d'un problème de base de données.
 */
#[AsCommand(
    name: 'app:diag:schema',
    description: 'Liste les entités mappées, leur table, et détecte les collisions'
)]
class DiagSchemaCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Diagnostic de schéma');

        $all = $this->em->getMetadataFactory()->getAllMetadata();

        $rows = [];
        $byTable = [];

        foreach ($all as $meta) {
            /* @var ClassMetadata $meta */
            if ($meta->isMappedSuperclass) {
                $rows[] = [$meta->getName(), '(superclasse mappée)', 'ignorée'];
                continue;
            }

            $table = $meta->getTableName();
            $schema = $meta->getSchemaName();
            $full = $schema ? $schema.'.'.$table : $table;

            $byTable[$full][] = $meta->getName();
            $rows[] = [$meta->getName(), $full, $meta->isInheritanceTypeNone() ? '' : 'héritage'];
        }

        $io->section(sprintf('%d classes chargées par le gestionnaire d\'entités', count($all)));
        $io->table(['Classe', 'Table', 'Note'], $rows);

        // --- Collisions ---------------------------------------------------
        $collisions = array_filter($byTable, static fn (array $classes) => count($classes) > 1);

        if ($collisions) {
            $io->section('COLLISIONS DÉTECTÉES');
            foreach ($collisions as $table => $classes) {
                $io->error(sprintf(
                    "La table \"%s\" est réclamée par %d classes :\n  - %s",
                    $table,
                    count($classes),
                    implode("\n  - ", $classes)
                ));
            }
            $io->writeln(
                "  C'est la cause exacte de l'erreur TableAlreadyExists.\n"
                ."  Une seule de ces classes doit rester mappée : retire l'autre\n"
                ."  du périmètre de doctrine.orm.mappings dans config/packages/doctrine.yaml."
            );
        } else {
            $io->success('Aucune collision de table côté mapping.');
        }

        // --- Doublons de classe -------------------------------------------
        $names = array_map(static fn (ClassMetadata $m) => $m->getName(), $all);
        $dupes = array_filter(array_count_values($names), static fn (int $n) => $n > 1);

        if ($dupes) {
            $io->error('Classes chargées plusieurs fois : '.implode(', ', array_keys($dupes)));
        }

        // --- Tables réellement en base ------------------------------------
        try {
            $conn = $this->em->getConnection();
            $manager = method_exists($conn, 'createSchemaManager')
                ? $conn->createSchemaManager()
                : $conn->getSchemaManager();

            $tables = $manager->listTableNames();
            sort($tables);

            $io->section(sprintf('%d tables présentes en base (%s)', count($tables), $conn->getDatabase()));
            $io->writeln('  '.($tables ? implode(', ', $tables) : '(aucune)'));

            $mapped = array_keys($byTable);
            sort($mapped);

            $missing = array_diff($mapped, $tables);
            $extra = array_diff($tables, $mapped, ['doctrine_migration_versions', 'messenger_messages']);

            if ($missing) {
                $io->warning('Tables mappées mais absentes de la base : '.implode(', ', $missing));
            }
            if ($extra) {
                $io->note(
                    'Tables présentes en base mais non mappées : '.implode(', ', $extra)
                    ."\nAttention : doctrine:schema:update --complete les supprimerait."
                );
            }
            if (!$missing && !$extra) {
                $io->success('Base et mapping alignés.');
            }
        } catch (\Throwable $e) {
            $io->warning('Base inaccessible : '.$e->getMessage());
        }

        return $collisions || $dupes ? Command::FAILURE : Command::SUCCESS;
    }
}
