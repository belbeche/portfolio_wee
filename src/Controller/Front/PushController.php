<?php

namespace App\Controller\Front;

use App\Entity\PushSubscription;
use App\Repository\PushSubscriptionRepository;
use App\Service\WebPush;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Abonnement du navigateur aux notifications push.
 */
class PushController extends AbstractController
{
    /**
     * @Route("/push/cle-publique", name="front_push_key", methods={"GET"})
     */
    public function key(WebPush $webPush): JsonResponse
    {
        try {
            return new JsonResponse(['key' => $webPush->publicKey()]);
        } catch (\Throwable $e) {
            error_log('[push] generation de cle impossible : '.$e->getMessage());

            return new JsonResponse(['key' => null], 503);
        }
    }

    /**
     * @Route("/push/abonnement", name="front_push_subscribe", methods={"POST"})
     */
    public function subscribe(Request $request, PushSubscriptionRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        $endpoint = (string) ($data['endpoint'] ?? '');
        $p256dh = (string) ($data['keys']['p256dh'] ?? '');
        $auth = (string) ($data['keys']['auth'] ?? '');

        if ('' === $endpoint || '' === $p256dh || '' === $auth || 0 !== strpos($endpoint, 'https://')) {
            return new JsonResponse(['ok' => false], 400);
        }

        $subscription = $repo->findOneByEndpoint($endpoint) ?? new PushSubscription();
        $subscription->setEndpoint($endpoint)->setP256dh($p256dh)->setAuth($auth);

        if (null !== $this->getUser()) {
            $subscription->setUserEmail($this->getUser()->getUserIdentifier());
        }

        $em->persist($subscription);
        $em->flush();

        return new JsonResponse(['ok' => true]);
    }
}
