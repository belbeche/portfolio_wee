<?php

namespace App\Controller\Back;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Tableau de bord de l'administration.
 *
 * L'ancienne version affichait un titre et un lien de retour a l'accueil.
 * Un tableau de bord doit repondre a une seule question quand on l'ouvre :
 * qu'est-ce qui demande mon attention maintenant.
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/admin/index", name="back_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('back/index/index.html.twig', [
            'derniersDevis' => $this->latest($em, 'App\Entity\Devis', 6),
            'derniersRappels' => $this->latest($em, 'App\Entity\CallbackRequest', 6),
            'derniersTickets' => $this->latest($em, 'App\Entity\Ticket', 6),
            'derniersContacts' => $this->latest($em, 'App\Entity\Contact', 5),
            'argent' => $this->argent($em),
        ]);
    }

    /**
     * Le suivi commercial, en euros.
     *
     * Un tableau de bord qui compte des lignes de contenu mais pas des euros
     * ne dit rien de l'etat reel de l'activite. Quatre chiffres suffisent, et
     * ils repondent aux quatre questions que l'on se pose vraiment :
     *   ce que j'ai chiffre et qui attend une reponse
     *   ce que j'ai facture et qui n'est pas encore paye
     *   ce que j'ai encaisse
     *   ce qui est en retard, donc ce que je dois relancer aujourd'hui
     *
     * @return array<string, float|int>
     */
    private function argent(EntityManagerInterface $em): array
    {
        $vide = [
            'devisChiffres' => 0.0, 'devisEnAttente' => 0,
            'facture' => 0.0, 'encaisse' => 0.0, 'attendu' => 0.0,
            'retard' => 0.0, 'nbRetard' => 0,
            'moisEncaisse' => 0.0, 'tauxReponse' => 0,
        ];

        try {
            // Les devis chiffres qui n'ont pas encore donne de facture.
            $devis = $em->createQuery(
                'SELECT COALESCE(SUM(d.prix), 0) AS total, COUNT(d.id) AS nb
                 FROM App\Entity\Devis d WHERE d.prix > 0'
            )->getSingleResult();

            $vide['devisChiffres'] = (float) $devis['total'];
            $vide['devisEnAttente'] = (int) $devis['nb'];
        } catch (\Throwable $e) {
            // Table absente ou schema pas encore a jour : on affiche zero
            // plutot que de faire tomber tout le tableau de bord.
        }

        try {
            $factures = $em->getRepository('App\Entity\Facture')->findAll();
            $debutMois = new \DateTime('first day of this month 00:00:00');

            foreach ($factures as $facture) {
                if ('annulee' === $facture->getStatut()) {
                    continue;
                }
                $vide['facture'] += $facture->getTotalTtc();
                $vide['encaisse'] += $facture->getMontantRegle();
                $vide['attendu'] += $facture->getResteADevoir();

                if ($facture->estEnRetard()) {
                    $vide['retard'] += $facture->getResteADevoir();
                    ++$vide['nbRetard'];
                }

                if (null !== $facture->getRegleeLe() && $facture->getRegleeLe() >= $debutMois) {
                    $vide['moisEncaisse'] += $facture->getMontantRegle();
                }
            }
        } catch (\Throwable $e) {
            // idem
        }

        try {
            $envois = $em->createQuery(
                'SELECT COUNT(e.id) AS envoyes,
                        SUM(CASE WHEN e.ouvertures > 0 THEN 1 ELSE 0 END) AS ouverts
                 FROM App\Entity\ProspectEnvoi e'
            )->getSingleResult();

            $envoyes = (int) $envois['envoyes'];
            $vide['tauxReponse'] = $envoyes > 0
                ? (int) round((int) $envois['ouverts'] / $envoyes * 100)
                : 0;
        } catch (\Throwable $e) {
            // idem
        }

        return $vide;
    }

    /**
     * Les N derniers enregistrements d'une entite, sans planter si la table
     * n'existe pas encore.
     */
    private function latest(EntityManagerInterface $em, string $class, int $limit): array
    {
        try {
            return $em->createQuery(sprintf('SELECT e FROM %s e ORDER BY e.id DESC', $class))
                ->setMaxResults($limit)
                ->getResult();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
