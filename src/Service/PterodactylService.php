<?php

namespace App\Service;

/**
 * Client de l'API officielle Pterodactyl (API Client, jeton ptlc_).
 *
 * Reference : https://pteroapi.com/docs
 *   GET {panel}/api/client                              -> liste des serveurs
 *   GET {panel}/api/client/servers/{id}/resources       -> etat et usage en direct
 *
 * En-tetes requis :
 *   Authorization: Bearer ptlc_xxx
 *   Accept: Application/vnd.pterodactyl.v1+json
 *   Content-Type: application/json
 *
 * Toutes les erreurs (reseau, jeton invalide, panneau eteint) se traduisent
 * par une liste vide : la page Supervision affiche alors son etat calme,
 * jamais une erreur brute au client.
 */
class PterodactylService
{
    private Settings $settings;

    /** Vrai si au moins un appel au panneau a echoue (reseau, jeton refuse). */
    private bool $failed = false;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }

    public function isConfigured(): bool
    {
        return '' !== $this->panelUrl() && '' !== $this->clientKey();
    }

    /**
     * La liste des serveurs du compte, chacun enrichi de son usage en direct,
     * au format attendu par la page Supervision :
     * name, description, status, suspended, limits{memory,disk,cpu}, usage{...}
     *
     * @return array<int, array<string, mixed>>
     */
    public function getServersWithUsage(int $maxServers = 15): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $this->failed = false;
        $servers = [];
        $page = 1;

        do {
            $data = $this->request(sprintf('/api/client?per_page=50&page=%d', $page));
            if (null === $data) {
                return $servers; // ce qu'on a deja, plutot que rien
            }

            error_log(sprintf(
                '[pterodactyl] page %d : %d serveur(s), total annonce %s',
                $page,
                count($data['data'] ?? []),
                (string) ($data['meta']['pagination']['total'] ?? '?')
            ));

            foreach (($data['data'] ?? []) as $item) {
                $servers[] = $item['attributes'] ?? [];
                if (count($servers) >= $maxServers) {
                    break 2;
                }
            }

            $totalPages = (int) ($data['meta']['pagination']['total_pages'] ?? 1);
            ++$page;
        } while ($page <= $totalPages);

        $out = [];
        foreach ($servers as $attributes) {
            $identifier = (string) ($attributes['identifier'] ?? '');
            $usage = '' !== $identifier
                ? $this->request(sprintf('/api/client/servers/%s/resources', $identifier))
                : null;

            $stats = $usage['attributes'] ?? [];
            $resources = $stats['resources'] ?? [];

            $out[] = [
                'identifier' => $identifier,
                'name' => $attributes['name'] ?? 'Serveur',
                'description' => $attributes['description'] ?? '',
                // current_state : running, starting, stopping, offline
                'status' => $stats['current_state'] ?? ($attributes['status'] ?? null),
                'suspended' => (bool) ($stats['is_suspended'] ?? ($attributes['is_suspended'] ?? false)),
                'limits' => [
                    'memory' => $attributes['limits']['memory'] ?? null, // en Mo
                    'disk' => $attributes['limits']['disk'] ?? null,     // en Mo
                    'cpu' => $attributes['limits']['cpu'] ?? null,       // en %
                ],
                'usage' => [
                    'memory_mb' => isset($resources['memory_bytes']) ? (int) round($resources['memory_bytes'] / 1048576) : null,
                    'disk_mb' => isset($resources['disk_bytes']) ? (int) round($resources['disk_bytes'] / 1048576) : null,
                    'cpu_percent' => isset($resources['cpu_absolute']) ? round((float) $resources['cpu_absolute'], 1) : null,
                    'uptime_ms' => $resources['uptime'] ?? null,
                ],
                'updated_at' => null,
            ];
        }

        return $out;
    }

    /**
     * Resume anonyme, destine a la page d'accueil publique.
     *
     * Aucun nom de serveur, aucune description, aucun identifiant : un
     * visiteur ne doit rien pouvoir deduire des clients heberges. Seulement
     * des compteurs, qui suffisent a montrer que la supervision est reelle.
     *
     * @return array{configure: bool, total: int, enLigne: int, cpu: float|null, memoireMo: int|null, verifieA: string}
     */
    public function publicSummary(int $maxServers = 30): array
    {
        $vide = [
            'configure' => false,
            'total' => 0,
            'enLigne' => 0,
            'cpu' => null,
            'memoireMo' => null,
            'verifieA' => (new \DateTime())->format('c'),
        ];

        if (!$this->isConfigured()) {
            return $vide;
        }

        $serveurs = $this->getServersWithUsage($maxServers);
        if ([] === $serveurs) {
            return $vide;
        }

        // La moyenne ne porte que sur les serveurs qui tournent. Inclure les
        // serveurs arretes, dont l'usage vaut zero, ferait mecaniquement
        // chuter la charge affichee : le chiffre serait juste au sens du
        // calcul et faux au sens de ce qu'il pretend decrire.
        $enLigne = 0;
        $cpu = 0.0;
        $memoire = 0;
        foreach ($serveurs as $serveur) {
            if ('running' !== ($serveur['status'] ?? null)) {
                continue;
            }
            ++$enLigne;
            $cpu += (float) ($serveur['usage']['cpu_percent'] ?? 0);
            $memoire += (int) ($serveur['usage']['memory_mb'] ?? 0);
        }

        return [
            'configure' => true,
            'total' => count($serveurs),
            'enLigne' => $enLigne,
            'cpu' => round($cpu / max(1, $enLigne), 1),
            'memoireMo' => $memoire,
            'verifieA' => (new \DateTime())->format('c'),
        ];
    }

    /**
     * Sous-ensemble visible par un client du site : les serveurs dont la
     * description contient son adresse e-mail. C'est la convention de
     * rattachement : sur le panneau, mets l'e-mail du client dans la
     * description de son serveur.
     */
    public function getServersForEmail(string $email, int $maxServers = 15): array
    {
        $email = mb_strtolower(trim($email));
        if ('' === $email) {
            return [];
        }

        return array_values(array_filter(
            $this->getServersWithUsage($maxServers),
            static fn (array $server) => str_contains(mb_strtolower((string) $server['description']), $email)
        ));
    }

    private function panelUrl(): string
    {
        return rtrim((string) $this->settings->get('pterodactyl_panel_url', ''), '/');
    }

    private function clientKey(): string
    {
        return (string) $this->settings->get('pterodactyl_client_key', '');
    }

    /** @return array<string, mixed>|null */
    private function request(string $path): ?array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->panelUrl().$path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            // Sans User-Agent, les pare-feux applicatifs (Cloudflare) repondent 403.
            CURLOPT_USERAGENT => 'walidbelbeche.fr-supervision/1.0',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$this->clientKey(),
                'Accept: Application/vnd.pterodactyl.v1+json',
                'Content-Type: application/json',
            ],
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if (false === $body || $status >= 400) {
            $this->failed = true;
            $extrait = is_string($body) ? mb_substr(preg_replace('/\s+/', ' ', strip_tags($body)), 0, 200) : '';
            error_log(sprintf('[pterodactyl] echec %s : HTTP %d, curl "%s", reponse "%s"', $path, $status, $error, $extrait));
            return null;
        }

        $data = json_decode((string) $body, true);

        return is_array($data) ? $data : null;
    }
}
