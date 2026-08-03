<?php

namespace App\Service;

use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Envoi de notifications web push, sans aucune dependance composer.
 *
 * Implementation en PHP + openssl de :
 *   - VAPID (RFC 8292) : jeton JWT signe en ES256 avec une cle P-256
 *   - le chiffrement du contenu (RFC 8291, schema aes128gcm)
 *
 * Les cles VAPID sont generees automatiquement au premier usage et rangees
 * dans les reglages (table app_setting) : rien a configurer a la main.
 */
class WebPush
{
    private Settings $settings;
    private EntityManagerInterface $em;

    public function __construct(Settings $settings, EntityManagerInterface $em)
    {
        $this->settings = $settings;
        $this->em = $em;
    }

    /** La cle publique VAPID (point P-256 brut, en base64url), pour le navigateur. */
    public function publicKey(): string
    {
        $this->ensureKeys();

        return (string) $this->settings->get('vapid_public_key', '');
    }

    /**
     * Envoie une notification a un abonnement.
     * Retourne null si succes, sinon un message d'erreur.
     * Supprime l'abonnement s'il est expire (404 ou 410).
     *
     * @param array{title?: string, body?: string, url?: string} $payload
     */
    public function send(PushSubscription $subscription, array $payload): ?string
    {
        $this->ensureKeys();

        $endpoint = $subscription->getEndpoint();
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $body = $this->encrypt($json, $subscription->getP256dh(), $subscription->getAuth());
            $jwt = $this->vapidJwt($endpoint);
        } catch (\Throwable $e) {
            return 'chiffrement impossible : '.$e->getMessage();
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'TTL: 86400',
                'Content-Type: application/octet-stream',
                'Content-Encoding: aes128gcm',
                'Content-Length: '.strlen($body),
                'Urgency: normal',
                sprintf('Authorization: vapid t=%s, k=%s', $jwt, $this->settings->get('vapid_public_key')),
            ],
        ]);

        $reponse = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $erreurCurl = curl_error($curl);
        curl_close($curl);

        if (404 === $status || 410 === $status) {
            // Abonnement mort : le navigateur a revoque ou desinstalle.
            $this->em->remove($subscription);
            $this->em->flush();

            return 'abonnement expire, supprime';
        }

        if (false === $reponse || $status < 200 || $status >= 300) {
            return sprintf('HTTP %d %s %s', $status, $erreurCurl, mb_substr((string) $reponse, 0, 120));
        }

        return null;
    }

    /* ------------------------------------------------------------ cles -- */

    private function ensureKeys(): void
    {
        if ('' !== (string) $this->settings->get('vapid_public_key', '')
            && '' !== (string) $this->settings->get('vapid_private_key', '')) {
            return;
        }

        $key = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if (false === $key) {
            throw new \RuntimeException('openssl ne peut pas generer de cle P-256');
        }

        openssl_pkey_export($key, $pemPrive);
        $details = openssl_pkey_get_details($key);
        $publicBrut = "\x04".self::pad32($details['ec']['x']).self::pad32($details['ec']['y']);

        $this->settings->set('vapid_private_key', $pemPrive);
        $this->settings->set('vapid_public_key', self::b64url($publicBrut));
    }

    /* ----------------------------------------------------------- vapid -- */

    private function vapidJwt(string $endpoint): string
    {
        $origine = parse_url($endpoint, PHP_URL_SCHEME).'://'.parse_url($endpoint, PHP_URL_HOST);

        $entete = self::b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $corps = self::b64url(json_encode([
            'aud' => $origine,
            'exp' => time() + 43200,
            'sub' => 'mailto:contact@walidbelbeche.fr',
        ]));

        $aSigner = $entete.'.'.$corps;
        $pem = (string) $this->settings->get('vapid_private_key');
        if (!openssl_sign($aSigner, $signatureDer, $pem, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('signature VAPID impossible');
        }

        return $aSigner.'.'.self::b64url(self::derVersBrut($signatureDer));
    }

    /** Convertit une signature ECDSA DER en 64 octets bruts r||s. */
    private static function derVersBrut(string $der): string
    {
        $pos = 2;
        if ("\x30" !== $der[0]) {
            throw new \RuntimeException('signature DER inattendue');
        }
        if (ord($der[1]) > 0x80) {
            $pos += ord($der[1]) - 0x80;
        }

        $lire = static function (string $der, int &$pos): string {
            if ("\x02" !== $der[$pos]) {
                throw new \RuntimeException('entier DER inattendu');
            }
            $longueur = ord($der[$pos + 1]);
            $valeur = substr($der, $pos + 2, $longueur);
            $pos += 2 + $longueur;

            return ltrim($valeur, "\x00");
        };

        $r = $lire($der, $pos);
        $s = $lire($der, $pos);

        return self::pad32($r).self::pad32($s);
    }

    /* ----------------------------------------- chiffrement RFC 8291 ----- */

    private function encrypt(string $clair, string $p256dhB64, string $authB64): string
    {
        $abonnePublic = self::b64urlDecode($p256dhB64);   // point non compresse, 65 octets
        $authSecret = self::b64urlDecode($authB64);       // 16 octets

        if (65 !== strlen($abonnePublic) || "\x04" !== $abonnePublic[0]) {
            throw new \RuntimeException('cle p256dh invalide');
        }

        // Paire ephemere du serveur
        $ephemere = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        $details = openssl_pkey_get_details($ephemere);
        $serveurPublic = "\x04".self::pad32($details['ec']['x']).self::pad32($details['ec']['y']);

        // Secret partage ECDH avec la cle publique de l'abonne
        $secret = openssl_pkey_derive(self::pointVersPem($abonnePublic), $ephemere, 32);
        if (false === $secret) {
            throw new \RuntimeException('ECDH impossible : '.openssl_error_string());
        }

        // Derivations HKDF du RFC 8291
        $ikm = hash_hkdf('sha256', $secret, 32, "WebPush: info\x00".$abonnePublic.$serveurPublic, $authSecret);
        $sel = random_bytes(16);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $sel);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00", $sel);

        // Un seul enregistrement : contenu + delimiteur 0x02
        $chiffre = openssl_encrypt($clair."\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
        if (false === $chiffre) {
            throw new \RuntimeException('chiffrement AES-GCM impossible');
        }

        // En-tete de codage : sel (16) + taille d'enregistrement (4) + longueur cle (1) + cle serveur (65)
        return $sel.pack('N', 4096).chr(65).$serveurPublic.$chiffre.$tag;
    }

    /** Transforme un point P-256 brut en cle publique PEM comprehensible par openssl. */
    private static function pointVersPem(string $point): string
    {
        $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200').$point;

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($der), 64, "\n").'-----END PUBLIC KEY-----';
    }

    /* ---------------------------------------------------------- outils -- */

    private static function pad32(string $binaire): string
    {
        return str_pad($binaire, 32, "\x00", STR_PAD_LEFT);
    }

    private static function b64url(string $binaire): string
    {
        return rtrim(strtr(base64_encode($binaire), '+/', '-_'), '=');
    }

    private static function b64urlDecode(string $texte): string
    {
        return (string) base64_decode(strtr($texte, '-_', '+/'), false);
    }
}
