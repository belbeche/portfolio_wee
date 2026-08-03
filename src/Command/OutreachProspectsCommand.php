<?php

namespace App\Command;

use App\Entity\Prospect;
use App\Entity\ProspectNote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

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
 * Garde-fous, non negociables :
 *   - simulation par defaut : rien ne part sans --envoyer
 *   - 15 envois maximum par execution (deliverabilite et serieux)
 *   - les adresses non verifiees (@a-completer...) sont toujours ignorees
 *   - chaque envoi est trace dans le journal du prospect
 */
class OutreachProspectsCommand extends Command
{
    protected static $defaultName = 'app:prospects:contacter';
    protected static $defaultDescription = 'Envoie les premiers contacts et les relances dues, avec garde-fous';

    private const MAX_PAR_EXECUTION = 15;

    private EntityManagerInterface $em;
    private MailerInterface $mailer;
    private Environment $twig;
    private string $projectDir;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer, Environment $twig, string $projectDir)
    {
        parent::__construct();
        $this->em = $em;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            ->addOption('envoyer', null, InputOption::VALUE_NONE, "Envoyer reellement. Sans cette option : simulation.")
            ->addOption('relances', null, InputOption::VALUE_NONE, 'Traiter les relances dues au lieu des premiers contacts')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Plafond de cette execution', (string) self::MAX_PAR_EXECUTION);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reel = (bool) $input->getOption('envoyer');
        $relances = (bool) $input->getOption('relances');
        $limit = max(1, min(self::MAX_PAR_EXECUTION, (int) $input->getOption('limit')));

        $repo = $this->em->getRepository(Prospect::class);

        if ($relances) {
            $candidats = array_filter(
                $repo->findBy(['status' => [Prospect::STATUS_CONTACTED, Prospect::STATUS_FOLLOWED_UP]]),
                static fn (Prospect $p) => $p->isFollowUpDue()
            );
            $gabarit = 'back/prospect/relance_prospect.html.twig';
            $sujet = 'Je reviens vers vous - Walid Belbeche, developpement web et mobile';
            $typeNote = 'Relance';
        } else {
            $candidats = $repo->findBy(['status' => Prospect::STATUS_TO_CONTACT], ['priority' => 'ASC']);
            $gabarit = 'back/prospect/welcome_prospect.html.twig';
            $sujet = 'Developpement web et mobile en marque blanche - Walid Belbeche';
            $typeNote = 'Premier contact';
        }

        // Jamais vers une adresse non verifiee.
        $candidats = array_values(array_filter(
            $candidats,
            static fn (Prospect $p) => false === strpos((string) $p->getEmail(), 'a-completer')
        ));

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

        $offre = $this->projectDir.'/public/docs/offre-walid-belbeche.pdf';
        $envoyes = 0;

        foreach ($candidats as $prospect) {
            $io->text(sprintf('  -> %-34s %-24s %s',
                mb_substr((string) $prospect->getCompany(), 0, 34),
                mb_substr((string) $prospect->getCity(), 0, 24),
                $prospect->getEmail()
            ));

            if (!$reel) {
                continue;
            }

            $email = (new Email())
                ->from('contact@walidbelbeche.fr')
                ->to($prospect->getEmail())
                ->bcc('wbelbeche.s@gmail.com')
                ->subject($sujet)
                ->html($this->twig->render($gabarit, [
                    'prospect' => $prospect,
                    'customMessage' => null,
                ]));

            if (!$relances && is_file($offre)) {
                $email->attachFromPath($offre, 'Offre - Walid Belbeche.pdf');
            }

            try {
                $this->mailer->send($email);
            } catch (\Exception $e) {
                $io->warning(sprintf('  Echec pour %s : %s', $prospect->getEmail(), $e->getMessage()));
                continue;
            }

            $prospect->registerContact();

            $note = (new ProspectNote())
                ->setProspect($prospect)
                ->setType('email')
                ->setContent(sprintf('[%s automatique] Sujet : %s%s', $typeNote, $sujet,
                    !$relances ? '. Offre PDF jointe.' : ''));
            $this->em->persist($note);
            ++$envoyes;
        }

        if ($reel) {
            $this->em->flush();
            $io->success(sprintf('%d e-mail(s) envoyes. Cadence de relance posee automatiquement (J+4 puis J+10).', $envoyes));
        } else {
            $io->note('Simulation terminee. Relance la commande avec --envoyer pour envoyer reellement.');
        }

        return Command::SUCCESS;
    }
}
