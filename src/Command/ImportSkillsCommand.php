<?php

namespace App\Command;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Import des competences et services.
 *
 *   php bin/console app:import:skills
 *   php bin/console app:import:skills --force   (ecrase les valeurs existantes)
 *
 * Meme principe que ta commande import:design : le referentiel est decrit
 * une fois dans le code, et la commande le pousse en base. Elle est
 * idempotente, tu peux la relancer autant de fois que tu veux.
 *
 * Pour ajouter une competence de facon durable, deux options :
 *   - la saisir dans /admin/competences (elle ne sera pas ecrasee) ;
 *   - l'ajouter au tableau ci-dessous, pour qu'elle suive le depot Git.
 */
#[AsCommand(
    name: 'app:import:skills',
    description: 'Importe le referentiel de competences et de services en base'
)]
class ImportSkillsCommand extends Command
{
    /**
     * nom, famille, niveau (1 a 5), annees, icone, description, mis en avant
     */
    private const REFERENTIEL = [
        // ------------------------------------------------------- mobile --
        ['React Native', 'mobile', 4, 2, 'spec-14.png', 'Applications iOS et Android a partir d\'une seule base de code', true],
        ['Publication App Store et Google Play', 'mobile', 4, 2, null, 'Comptes developpeur, fiches, conformite, soumission et suivi', true],
        ['Expo et TestFlight', 'mobile', 4, 2, null, 'Distribution de versions de test au client', false],

        // ------------------------------------------------------ back-end --
        ['Symfony', 'backend', 5, 9, 'spec-4.png', 'Applications metier, API et back-offices sur mesure', true],
        ['PHP', 'backend', 5, 12, 'spec-1.png', 'Le socle de la majorite de mes projets depuis 2014', true],
        ['API REST', 'backend', 5, 8, null, 'Conception et documentation d\'interfaces consommees par le web et le mobile', false],
        ['Doctrine ORM', 'backend', 4, 9, null, 'Modelisation de donnees, migrations versionnees', false],
        ['MySQL et PostgreSQL', 'backend', 4, 10, null, 'Conception de schemas, optimisation de requetes', false],
        ['Node.js', 'backend', 3, 4, 'spec-12.png', 'Services legers et outillage', false],

        // ----------------------------------------------------- front-end --
        ['JavaScript', 'frontend', 4, 10, null, 'Interactions, appels d\'API, composants sur mesure', false],
        ['React', 'frontend', 4, 4, 'spec-14.png', 'Interfaces web reactives', false],
        ['Vue.js', 'frontend', 3, 3, 'spec-5.svg', 'Interfaces web reactives', false],
        ['Next.js', 'frontend', 3, 2, 'spec-10.png', 'Rendu cote serveur et referencement', false],
        ['Twig', 'frontend', 5, 9, null, 'Gabarits Symfony, composants reutilisables', false],
        ['Bootstrap et CSS moderne', 'frontend', 4, 10, null, 'Mise en page responsive, systemes de design', false],

        // -------------------------------------------------- infrastructure --
        ['Docker', 'infra', 4, 5, 'spec-20.png', 'Environnements reproductibles du poste au serveur', true],
        ['Hebergement et supervision', 'infra', 4, 6, null, 'Serveurs, sauvegardes verifiees, surveillance par API', true],
        ['Git et integration continue', 'infra', 4, 9, null, 'Versionnement, branches, deploiement controle', false],
        ['Securite applicative', 'infra', 4, 5, null, 'En-tetes HTTP, CSP, anti force brute, RGPD', true],

        // -------------------------------------------------------- methode --
        ['Cadrage et cahier des charges', 'method', 5, 8, null, 'Specifications fonctionnelles et user stories avec criteres d\'acceptation', true],
        ['Conduite de projet par jalons', 'method', 5, 8, null, 'Paiement et validation etape par etape', true],
        ['Documentation technique', 'method', 5, 8, null, 'Un projet reprenable par un autre developpeur', true],
        ['Relation client', 'method', 5, 12, null, 'Un canal par usage, une trace ecrite pour chaque decision', false],
    ];

    private EntityManagerInterface $em;
    private SkillRepository $skills;

    public function __construct(EntityManagerInterface $em, SkillRepository $skills)
    {
        parent::__construct();
        $this->em = $em;
        $this->skills = $skills;
    }

    protected function configure(): void
    {
        $this->addOption(
            'force', 'f', InputOption::VALUE_NONE,
            'Met a jour les competences deja presentes au lieu de les laisser telles quelles'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $io->title('Import du referentiel de competences');

        $crees = 0;
        $majs = 0;
        $ignores = 0;
        $position = 0;

        foreach (self::REFERENTIEL as [$nom, $famille, $niveau, $annees, $icone, $description, $enAvant]) {
            ++$position;

            $skill = $this->skills->findOneBy(['name' => $nom]);

            if ($skill && !$force) {
                ++$ignores;
                continue;
            }

            if (!$skill) {
                $skill = new Skill();
                $skill->setName($nom);
                ++$crees;
            } else {
                ++$majs;
            }

            $skill->setSkillGroup($famille)
                ->setLevel($niveau)
                ->setYearsOfExperience($annees)
                ->setIcon($icone)
                ->setDescription($description)
                ->setHighlighted($enAvant)
                ->setVisible(true)
                ->setPosition($position);

            $this->em->persist($skill);
        }

        $this->em->flush();

        $io->table(
            ['Creees', 'Mises a jour', 'Deja presentes'],
            [[$crees, $majs, $ignores]]
        );

        if ($ignores > 0 && !$force) {
            $io->note(
                sprintf(
                    '%d competence(s) existaient deja et n\'ont pas ete touchees. '
                    .'Relance avec --force pour les aligner sur le referentiel.',
                    $ignores
                )
            );
        }

        $io->success(sprintf(
            '%d competences en base, dont %d mises en avant sur l\'accueil.',
            count($this->skills->findAll()),
            count($this->skills->findHighlighted(99))
        ));

        $io->writeln('  Elles sont modifiables une par une dans /admin/competences.');

        return Command::SUCCESS;
    }
}
