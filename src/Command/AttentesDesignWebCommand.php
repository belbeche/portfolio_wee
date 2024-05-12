<?php

namespace App\Command;


use App\Entity\AttenteDesignWeb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * run : php bin/console import:design
 */
class AttentesDesignWebCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'import:design';

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('La command permet l\'import des designs.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $symfonyStyle = new SymfonyStyle($input, $output);
        $progressBar = new ProgressBar($output, 1000000);

        $symfonyStyle->title('<info>.......</info>');

        $progressBar->start();
        $startTime = time();

        /* --------------------------- Start of the logic ----------------------------------- */
        $current = 0;

        $attentesDesignWeb = [
            ['label' => 'Design standard'],
            ['label' => 'Avec vos propres maquettes'],
            ['label' => 'Design avancé'],
            ['label' => 'Design orienté vers le mobile'],
            ['label' => 'Design animé ou interactif'],
            ['label' => 'Thème sombre'],
            ['label' => 'Thème clair'],
            ['label' => 'Design axé sur l’accessibilité'],
            ['label' => 'Design minimaliste'],
            ['label' => 'Branding complet inclus'],
            ['label' => 'Personnalisation avancée'],
        ];

        foreach ($attentesDesignWeb as $value) {
            $attenteDesignWeb = $this->entityManager->getRepository(AttenteDesignWeb::class)->findOneBy(
                ['label' => $value['label']]
            );

            if (!$attenteDesignWeb) {
                $attenteDesignWeb = new AttenteDesignWeb();

                $attenteDesignWeb->setLabel($value['label']);

                $this->entityManager->persist($attenteDesignWeb);
                $current++;
            }
        }
        $this->entityManager->flush();

        /* --------------------------- End of the logic ----------------------------------- */

        $symfonyStyle->newLine();
        $symfonyStyle->newLine();

        $endTime = time();
        $resTime = $endTime - $startTime;

        $symfonyStyle->text(' > <comment>Memory usage : ' . memory_get_usage() . '</comment>');
        $symfonyStyle->text(' > <info>OK duration : ' . $resTime . ' seconds</info>');
        $symfonyStyle->text(' > <comment>Nombre de lignes insérées : ' . ($current) . '</comment>');

        return Command::SUCCESS;
    }
}