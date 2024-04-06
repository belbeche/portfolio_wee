<?php

namespace App\Service;

class CurlService
{
    public function getResources(string $url, string $token): array
    {
        $resources = $this->connection($url,$token);
        $resourcesFilter = [];

        foreach ($resources['data'] as $resource) {
            foreach ($resource['attributes']['relationships']['servers']['data'] as $server) {
                $resourcesFilter[] = [
                    'name' => $server['attributes']['name'],
                    'description' => $server['attributes']['description'],
                    'status' => $server['attributes']['status'],
                    'suspended' => $server['attributes']['suspended'],
                    'wordpress' => $server['attributes']['container']['environment']['WORDPRESS'],
                    'updated_at' => $server['attributes']['updated_at'],
                    'created_at' => $server['attributes']['created_at'],
                    'limits' => [
                        'memory' => $server['attributes']['limits']['memory'],
                        'swap' => $server['attributes']['limits']['swap'],
                        'disk' => $server['attributes']['limits']['disk'],
                        'io' => $server['attributes']['limits']['io'],
                        'cpu' => $server['attributes']['limits']['cpu'],
                        'oom_disabled' => $server['attributes']['limits']['oom_disabled'],
                    ]
                ];
            }
        }

        return $resourcesFilter;
    }

    private function connection(string $url,string $token)
    {
        $options = [
            'Authorization: Bearer '. $token,
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        return $this->callApi(
            $url,
            $options
        );
    }

    private function callApi(string $url, array $options)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $options
        ]);

        $data = curl_exec($curl);

        if ($data === false) {
            curl_error($curl);
        }

        curl_close($curl);

        return json_decode($data, true);
    }
}