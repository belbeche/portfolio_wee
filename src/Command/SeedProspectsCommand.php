<?php

namespace App\Command;

use App\Entity\Prospect;
use App\Entity\ProspectNote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Charge dans le pipeline les 19 agences normandes recensees lors de
 * l'etude de prospection, avec leur qualification et leurs notes.
 *
 *     php bin/console app:prospects:seed-normandie
 *
 * La commande est rejouable : une agence deja presente (meme e-mail)
 * est ignoree, jamais dupliquee.
 */
class SeedProspectsCommand extends Command
{
    protected static $defaultName = 'app:prospects:seed-normandie';
    protected static $defaultDescription = 'Charge les 19 agences normandes qualifiees dans le pipeline';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /** [agence, ville, site, telephone, priorite, note] */
    private const AGENCES = [
        ['Crealys Web', 'Évreux', 'crealys-web.fr', '02 32 39 61 24', 'P1',
         "Offre mobile : NON, vérifié. Web + com + imprimerie. Entreprise individuelle avec réseau de partenaires : le profil qui sous-traite par nature."],
        ['MCN', 'Évreux / Louviers', 'mcn-info.com', '02 32 18 21 05', 'P1',
         "Offre mobile : NON, vérifié. 3 pôles : dev, infogérance, com. Fait déjà du dev sur mesure et de l'intégration d'API : comprendra l'offre immédiatement."],
        ['Lapis Lazuli', 'Vernon / Évreux / Louviers', 'lapilazuli.net', null, 'P2',
         "Couvre 3 villes de l'Eure. Agence de communication : souvent zéro compétence technique en interne."],
        ['DBMC Web', 'Évreux', 'dbmcweb.fr', null, 'P2',
         'Positionnée création de site + SEO.'],
        ['Abc Idea', 'Évreux', 'abc-idea.com', null, 'P2', null],
        ['BLD Web Agency', 'Évreux / Louviers', 'bldwebagency.fr', null, 'P2',
         "Couvre tout l'Eure via des pages par ville : signe d'une stratégie volume."],
        ['IDEAXE', 'Évreux (100% en ligne)', 'ideaxe.fr', null, 'P2',
         'Agence full remote : plus ouverte à un partenaire externe.'],
        ['123web', 'Eure', '123web.fr', null, 'P2', null],
        ['WAI31', 'Évreux', 'wai31.fr', null, 'P3',
         "Affiche déjà apps + SaaS. À contacter quand même : vérifier s'il y a des réalisations mobile réelles au portfolio."],
        ['Studionet', 'Rouen / Paris', 'studionet.fr', null, 'P2',
         'Sur-mesure et intranet. Structure plus grosse : cycle plus long mais budgets supérieurs.'],
        ['Works Agency', 'Rouen', 'works-agency.fr', null, 'P2',
         'Orientée conversion / marketing : profil typique sans dev mobile interne.'],
        ['Imagospirit', 'Rouen', 'imagospirit.com', null, 'P2',
         "Annonce « applications web » et couvre 76 + 27. Fort potentiel : le pas vers le mobile est court."],
        ['Webidibou', 'Rouen', 'webidibou.fr', null, 'P2', null],
        ['Highfive', 'Caen', 'highfive.fr', null, 'P2',
         'Agence web + communication digitale.'],
        ['Nouveau Regard', 'Caen', 'nouveau-regard.fr', null, 'P2',
         'Créations digitales + webmarketing.'],
        ['Digidream', 'Caen', 'digidream-communication.com', null, 'P2', null],
        ['Digibase Web', 'Normandie', 'digibase-web.fr', null, 'P2',
         'Se présente comme agence régionale.'],
        ["Becom'in", 'Caen', 'becom-in.fr', null, 'P3',
         "Fait déjà du mobile. Angle différent : renfort ponctuel en surcharge, pas remplacement."],
        ['FMOSYS', 'Le Havre', 'fmosys.fr', null, 'P3',
         "Même angle que Becom'in : proposer du débordement de charge."],
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repo = $this->em->getRepository(Prospect::class);
        $crees = 0;
        $ignores = 0;

        foreach (self::AGENCES as [$nom, $ville, $site, $tel, $priorite, $note]) {
            $email = 'contact@'.$site;

            if ($repo->findOneBy(['email' => $email])) {
                ++$ignores;
                continue;
            }

            $prospect = (new Prospect())
                ->setEmail($email)
                ->setCompany($nom)
                ->setCity($ville)
                ->setWebsite('https://'.$site)
                ->setPhone($tel)
                ->setPriority($priorite)
                ->setSource('Étude prospection Normandie, août 2026')
                ->setNotes("E-mail générique déduit du domaine, à vérifier avant le premier envoi.");
            $prospect->setServices(['mobile_app']);

            $this->em->persist($prospect);

            if ($note) {
                $journal = (new ProspectNote())
                    ->setProspect($prospect)
                    ->setType('note')
                    ->setContent('[Qualification initiale] '.$note);
                $this->em->persist($journal);
            }

            ++$crees;
            $io->text(sprintf('  + %-20s %-28s %s', $nom, $ville, $priorite));
        }

        $this->em->flush();
        $io->success(sprintf('%d prospect(s) ajoutés, %d déjà présents.', $crees, $ignores));
        $io->note("Priorités : P1 = pas d'offre mobile (cible idéale), P2 = à qualifier, P3 = angle renfort de charge.");

        return Command::SUCCESS;
    }
}
