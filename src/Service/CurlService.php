<?php

namespace App\Service;

/**
 * Interrogation de l'API du panneau d'hebergement.
 *
 * La version precedente supposait que la reponse contenait toujours la
 * structure attendue. Des que l'API etait injoignable ou repondait une
 * erreur, $resources['data'] valait null et le foreach levait une exception
 * qui remontait jusqu'a l'utilisateur.
 *
 * Une dependance externe echoue toujours un jour : le code doit le prevoir.
 */
class CurlService
{
    public function getResources(string $url, string $token): array
    {
        $resources = $this->connection($url, $token);

        if (!is_array($resources['data'] ?? null)) {
            return [];
        }

        $filtered = [];

        foreach ($resources['data'] as $resource) {
            $servers = $resource['attributes']['relationships']['servers']['data'] ?? null;

            if (!is_array($servers)) {
                continue;
            }

            foreach ($servers as $server) {
                $a = $server['attributes'] ?? null;

                if (!is_array($a)) {
                    continue;
                }

                $limits = $a['limits'] ?? [];

                $filtered[] = [
                    'name' => $a['name'] ?? null,
                    'description' => $a['description'] ?? null,
                    'status' => $a['status'] ?? null,
                    'suspended' => $a['suspended'] ?? false,
                    'wordpress' => $a['container']['environment']['WORDPRESS'] ?? null,
                    'updated_at' => $a['updated_at'] ?? null,
                    'created_at' => $a['created_at'] ?? null,
                    'limits' => [
                        'memory' => $limits['memory'] ?? null,
                        'swap' => $limits['swap'] ?? null,
                        'disk' => $limits['disk'] ?? null,
                        'io' => $limits['io'] ?? null,
                        'cpu' => $limits['cpu'] ?? null,
                        'oom_disabled' => $limits['oom_disabled'] ?? null,
                    ],
                ];
            }
        }

        return $filtered;
    }

    private function connection(string $url, string $token): ?array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$token,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (false === $response || $status < 200 || $status >= 300) {
            return null;
        }

        $decoded = json_decode((string) $response, true);

        return is_array($decoded) ? $decoded : null;
    }
}
