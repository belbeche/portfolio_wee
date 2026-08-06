<?php

namespace App\Command;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Trouve les lignes orphelines qui empêchent de reposer les clés étrangères.
 *
 *   php bin/console app:base:orphelins              (rapport seul, ne touche à rien)
 *   php bin/console app:base:orphelins --corriger   (répare)
 *
 * POURQUOI CETTE COMMANDE EXISTE
 *
 * Après une restauration, doctrine:schema:update échoue sur :
 *
 *   Cannot add or update a child row: a foreign key constraint fails
 *
 * Ce message est mal compris. Il ne dit pas que le schéma est faux. Il dit
 * que la table enfant contient des lignes qui désignent un parent absent.
 * MySQL refuse de poser la contrainte parce qu'elle serait violée dès la
 * première seconde. Autrement dit, ce n'est pas le schéma qu'il faut
 * corriger, ce sont les données.
 *
 * doctrine:schema:update s'arrête à la première contrainte en échec, ce qui
 * oblige à relancer une fois par problème sans jamais voir l'ensemble. Cette
 * commande les liste toutes d'un coup, sur toutes les tables.
 *
 * LA PRÉCAUTION QUI COMPTE
 *
 * Une table parente vide alors que la table enfant est pleine ne veut
 * généralement pas dire « ces lignes sont des déchets ». Ça veut dire que la
 * restauration n'est pas allée au bout. Supprimer les enfants à ce
 * moment-là transforme une restauration incomplète en perte définitive.
 *
 * La commande refuse donc de corriger une table dont le parent est
 * entièrement vide, sauf si on insiste avec --meme-si-parent-vide.
 */
#[AsCommand(
    name: 'app:base:orphelins',
    description: 'Liste, et corrige au besoin, les lignes qui pointent vers un parent absent'
)]
class OrphelinsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addOption('corriger', null, InputOption::VALUE_NONE, 'Applique la réparation au lieu de seulement la décrire')
            ->addOption('meme-si-parent-vide', null, InputOption::VALUE_NONE, 'Autorise la correction même quand la table parente est vide')
            ->addOption('table', null, InputOption::VALUE_REQUIRED, 'Ne traiter que cette table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $corriger = (bool) $input->getOption('corriger');
        $forcer = (bool) $input->getOption('meme-si-parent-vide');
        $filtre = $input->getOption('table');

        $io->title($corriger ? 'Réparation des références orphelines' : 'Références orphelines (rapport seul)');

        $cnx = $this->em->getConnection();
        $sm = $cnx->createSchemaManager();
        $presentes = array_map('strtolower', $sm->listTableNames());

        $outil = new SchemaTool($this->em);
        $cible = $outil->getSchemaFromMetadata($this->em->getMetadataFactory()->getAllMetadata());

        $lignes = [];
        $aCorriger = [];
        $ignorees = [];

        foreach ($cible->getTables() as $table) {
            $nom = $table->getName();
            if ($filtre && strtolower($filtre) !== strtolower($nom)) {
                continue;
            }
            if (!in_array(strtolower($nom), $presentes, true)) {
                continue; // la table n'existe pas encore en base : rien à nettoyer
            }

            foreach ($table->getForeignKeys() as $fk) {
                /* @var ForeignKeyConstraint $fk */
                $locales = $fk->getLocalColumns();
                $etrangeres = $fk->getForeignColumns();
                $parent = $fk->getForeignTableName();

                if (1 !== count($locales) || 1 !== count($etrangeres)) {
                    $ignorees[] = sprintf('%s : clé composée, à vérifier à la main', $nom);
                    continue;
                }
                if (!in_array(strtolower($parent), $presentes, true)) {
                    $ignorees[] = sprintf('%s : la table parente %s est absente', $nom, $parent);
                    continue;
                }

                $col = $locales[0];
                $ref = $etrangeres[0];

                $q = static fn (string $i): string => '`'.str_replace('`', '', $i).'`';

                $orphelins = (int) $cnx->fetchOne(sprintf(
                    'SELECT COUNT(*) FROM %s e LEFT JOIN %s p ON e.%s = p.%s WHERE e.%s IS NOT NULL AND p.%s IS NULL',
                    $q($nom), $q($parent), $q($col), $q($ref), $q($col), $q($ref)
                ));

                if (0 === $orphelins) {
                    continue;
                }

                $totalEnfant = (int) $cnx->fetchOne('SELECT COUNT(*) FROM '.$q($nom));
                $totalParent = (int) $cnx->fetchOne('SELECT COUNT(*) FROM '.$q($parent));

                // La nullabilité se lit sur la table réelle, pas sur le schéma
                // visé : c'est elle qui décide si on peut vider la référence
                // au lieu de supprimer la ligne.
                $nullable = true;
                foreach ($sm->listTableColumns($nom) as $c) {
                    if (strtolower($c->getName()) === strtolower($col)) {
                        $nullable = !$c->getNotnull();
                    }
                }

                $geste = $nullable ? 'vider la référence' : 'supprimer la ligne';
                $alerte = 0 === $totalParent ? ' PARENT VIDE' : '';

                $lignes[] = [
                    $nom,
                    $col.' -> '.$parent.'.'.$ref,
                    $orphelins.' / '.$totalEnfant,
                    $totalParent.$alerte,
                    $geste,
                ];

                $aCorriger[] = compact('nom', 'col', 'parent', 'ref', 'nullable', 'orphelins', 'totalParent');
            }
        }

        if ([] === $lignes) {
            $io->success('Aucune référence orpheline. Tu peux relancer doctrine:schema:update --force.');

            return Command::SUCCESS;
        }

        $io->table(
            ['Table', 'Référence', 'Orphelines / total', 'Lignes du parent', 'Réparation'],
            $lignes
        );

        foreach (array_unique($ignorees) as $i) {
            $io->warning($i);
        }

        $vides = array_filter($aCorriger, static fn (array $c): bool => 0 === $c['totalParent']);
        if ([] !== $vides && !$forcer) {
            $io->caution([
                'Au moins une table parente est complètement vide.',
                "C'est le signe habituel d'une restauration qui n'est pas allée au bout,",
                'pas de données à jeter. Vérifie ta sauvegarde et recharge les tables',
                'manquantes AVANT de corriger : ces lignes enfant sont peut-être la',
                'seule copie qui te reste.',
                'Si tu es sûr de toi, ajoute --meme-si-parent-vide.',
            ]);
        }

        if (!$corriger) {
            $io->note('Rapport seul, rien n\'a été modifié. Ajoute --corriger pour appliquer.');

            return Command::SUCCESS;
        }

        $total = 0;
        foreach ($aCorriger as $c) {
            if (0 === $c['totalParent'] && !$forcer) {
                $io->writeln(sprintf('  ignoré  %s.%s (parent vide)', $c['nom'], $c['col']));
                continue;
            }

            $q = static fn (string $i): string => '`'.str_replace('`', '', $i).'`';

            // La sous-requete est enveloppee dans une table derivee. Sans ca,
            // une cle qui pointe vers sa propre table (une categorie et sa
            // categorie parente, par exemple) declenche l'erreur MySQL 1093 :
            // « You can't specify target table for update in FROM clause ».
            $source = sprintf('(SELECT %s FROM (SELECT %s FROM %s) AS src)',
                $q($c['ref']), $q($c['ref']), $q($c['parent']));

            $sql = $c['nullable']
                ? sprintf(
                    'UPDATE %s SET %s = NULL WHERE %s IS NOT NULL AND %s NOT IN %s',
                    $q($c['nom']), $q($c['col']), $q($c['col']), $q($c['col']), $source
                )
                : sprintf(
                    'DELETE FROM %s WHERE %s IS NOT NULL AND %s NOT IN %s',
                    $q($c['nom']), $q($c['col']), $q($c['col']), $source
                );

            $touchees = (int) $cnx->executeStatement($sql);
            $total += $touchees;
            $io->writeln(sprintf(
                '  %-24s %-28s %d ligne(s) %s',
                $c['nom'], $c['col'], $touchees, $c['nullable'] ? 'détachée(s)' : 'supprimée(s)'
            ));
        }

        $io->success($total.' ligne(s) traitée(s). Relance maintenant : doctrine:schema:update --force');

        return Command::SUCCESS;
    }
}
