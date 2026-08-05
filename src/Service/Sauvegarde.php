<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Sauvegarde de la base et des fichiers envoyes.
 *
 * L'export SQL est ecrit en PHP pur, sans mysqldump : le conteneur de
 * production n'a pas les outils clients MySQL, et dependre d'un binaire
 * absent, c'est une sauvegarde qui n'existe que sur le papier.
 *
 * Chaque sauvegarde est une archive .zip datee contenant :
 *   base.sql        la structure et les donnees
 *   fichiers/       le contenu de public/uploads
 *   informations.txt un resume lisible
 */
class Sauvegarde
{
    /** Nombre d'archives conservees : au-dela, la plus ancienne est effacee. */
    public const A_CONSERVER = 7;

    /** Au-dela de cette taille, une table est exportee par tranches. */
    private const LIGNES_PAR_LOT = 500;

    /**
     * En dessous de cet espace libre, on ne sauvegarde pas.
     *
     * Une sauvegarde qui remplit le disque fait tomber le site et parfois le
     * serveur de base avec lui. Ce serait le comble : le mecanisme cense
     * proteger les donnees devenu la cause de la panne.
     */
    private const DISQUE_MINI_MO = 400;

    private Connection $connexion;
    private string $projectDir;

    public function __construct(Connection $connexion, string $projectDir)
    {
        $this->connexion = $connexion;
        $this->projectDir = $projectDir;
    }

    public function dossier(): string
    {
        $dossier = $this->projectDir.'/var/sauvegardes';
        if (!is_dir($dossier)) {
            @mkdir($dossier, 0775, true);
        }

        return $dossier;
    }

    /**
     * La liste des archives presentes, de la plus recente a la plus ancienne.
     *
     * @return array<int, array{nom: string, chemin: string, octets: int, date: \DateTimeImmutable}>
     */
    public function archives(): array
    {
        $archives = [];
        foreach (glob($this->dossier().'/sauvegarde-*.zip') ?: [] as $chemin) {
            $archives[] = [
                'nom' => basename($chemin),
                'chemin' => $chemin,
                'octets' => (int) @filesize($chemin),
                'date' => (new \DateTimeImmutable())->setTimestamp((int) @filemtime($chemin)),
            ];
        }

        usort($archives, static fn ($a, $b) => $b['date'] <=> $a['date']);

        return $archives;
    }

    /** L'age de la derniere sauvegarde, en heures. null si aucune. */
    public function ageDerniereEnHeures(): ?float
    {
        $archives = $this->archives();
        if ([] === $archives) {
            return null;
        }

        return round((time() - $archives[0]['date']->getTimestamp()) / 3600, 1);
    }

    /**
     * Cree une archive. Retourne son chemin, ou leve une exception.
     */
    public function creer(): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException("L'extension PHP zip est absente : impossible de creer l'archive.");
        }

        $libre = @disk_free_space($this->projectDir);
        if (false !== $libre && null !== $libre) {
            $libreMo = (int) round($libre / 1048576);
            if ($libreMo < self::DISQUE_MINI_MO) {
                // On fait d'abord le menage, des fois que ce soient les vieilles
                // archives qui occupent la place.
                $this->faireLePlace();
                $libreMo = (int) round(((float) @disk_free_space($this->projectDir)) / 1048576);
            }

            if ($libreMo < self::DISQUE_MINI_MO) {
                throw new \RuntimeException(sprintf(
                    'Sauvegarde annulee : il ne reste que %d Mo libres (seuil : %d Mo). '
                    .'Remplir le disque ferait tomber le site, ce qui serait pire que de manquer une sauvegarde.',
                    $libreMo,
                    self::DISQUE_MINI_MO
                ));
            }
        }

        $horodatage = (new \DateTime())->format('Y-m-d_H-i-s');
        $chemin = $this->dossier().'/sauvegarde-'.$horodatage.'.zip';

        $zip = new \ZipArchive();
        if (true !== $zip->open($chemin, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException('Impossible de creer '.$chemin);
        }

        $sql = $this->exporterBase();
        $zip->addFromString('base.sql', $sql);

        $fichiers = $this->ajouterFichiers($zip);

        $zip->addFromString('informations.txt', sprintf(
            "Sauvegarde walidbelbeche.fr\nDate : %s\nBase : %d octets de SQL\nFichiers : %d\nPHP : %s\n",
            (new \DateTime())->format('d/m/Y H:i:s'),
            strlen($sql),
            $fichiers,
            PHP_VERSION
        ));

        $zip->close();
        $this->faireLePlace();

        return $chemin;
    }

    /** Efface les archives excedentaires, la plus ancienne d'abord. */
    public function faireLePlace(): int
    {
        $archives = $this->archives();
        $effacees = 0;
        foreach (array_slice($archives, self::A_CONSERVER) as $archive) {
            if (@unlink($archive['chemin'])) {
                ++$effacees;
            }
        }

        return $effacees;
    }

    /**
     * L'export SQL complet : structure puis donnees, table par table.
     */
    private function exporterBase(): string
    {
        $sortie = "-- Sauvegarde walidbelbeche.fr\n"
            ."-- ".(new \DateTime())->format('c')."\n"
            ."SET NAMES utf8mb4;\n"
            ."SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $tables = $this->connexion->fetchFirstColumn('SHOW TABLES');

        foreach ($tables as $table) {
            $nom = (string) $table;
            $creation = $this->connexion->fetchAssociative('SHOW CREATE TABLE '.$this->proteger($nom));
            $definition = $creation['Create Table'] ?? ($creation['Create View'] ?? null);
            if (null === $definition) {
                continue;
            }

            $sortie .= "\n-- Table ".$nom."\n";
            $sortie .= 'DROP TABLE IF EXISTS '.$this->proteger($nom).";\n";
            $sortie .= $definition.";\n\n";

            $total = (int) $this->connexion->fetchOne('SELECT COUNT(*) FROM '.$this->proteger($nom));
            for ($debut = 0; $debut < $total; $debut += self::LIGNES_PAR_LOT) {
                $lignes = $this->connexion->fetchAllAssociative(sprintf(
                    'SELECT * FROM %s LIMIT %d OFFSET %d',
                    $this->proteger($nom),
                    self::LIGNES_PAR_LOT,
                    $debut
                ));

                foreach ($lignes as $ligne) {
                    $colonnes = array_map([$this, 'proteger'], array_keys($ligne));
                    $valeurs = array_map([$this, 'litteral'], array_values($ligne));
                    $sortie .= sprintf(
                        "INSERT INTO %s (%s) VALUES (%s);\n",
                        $this->proteger($nom),
                        implode(', ', $colonnes),
                        implode(', ', $valeurs)
                    );
                }
            }
        }

        return $sortie."\nSET FOREIGN_KEY_CHECKS = 1;\n";
    }

    /** Ajoute public/uploads a l'archive. Retourne le nombre de fichiers. */
    private function ajouterFichiers(\ZipArchive $zip): int
    {
        $racine = $this->projectDir.'/public/uploads';
        if (!is_dir($racine)) {
            return 0;
        }

        $ajoutes = 0;
        $parcours = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($racine, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($parcours as $fichier) {
            if (!$fichier->isFile()) {
                continue;
            }
            // Les tres gros fichiers font exploser l'archive et la memoire :
            // mieux vaut une sauvegarde utile qu'une sauvegarde impossible.
            if ($fichier->getSize() > 12 * 1024 * 1024) {
                continue;
            }
            $relatif = 'fichiers/'.ltrim(str_replace($racine, '', $fichier->getPathname()), '/\\');
            if ($zip->addFile($fichier->getPathname(), $relatif)) {
                ++$ajoutes;
            }
        }

        return $ajoutes;
    }

    private function proteger(string $identifiant): string
    {
        return '`'.str_replace('`', '``', $identifiant).'`';
    }

    /** @param mixed $valeur */
    private function litteral($valeur): string
    {
        if (null === $valeur) {
            return 'NULL';
        }
        if (is_bool($valeur)) {
            return $valeur ? '1' : '0';
        }
        if (is_int($valeur) || is_float($valeur)) {
            return (string) $valeur;
        }

        return $this->connexion->quote((string) $valeur);
    }
}
