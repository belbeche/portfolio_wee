<?php

// src/Controller/Front/LocaleController.php
namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

class LocaleController
{
    private $translator;

    public function __construct(TranslatorBagInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route('/change-locale/{locale}', name: 'change_locale', methods: ['GET','POST'])]
    public function changeLocale(string $locale, Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->set('_locale', $locale);

        return new JsonResponse(['success' => true]);
    }

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        // 1. Valider la locale
        if (!in_array($locale, ['fr', 'en'])) {
            return new JsonResponse(['status' => 'error'], 400);
        }

        // 2. Préparer toutes les traductions
        $translations = $this->getAllTranslations($locale);

        // 3. Retourner une réponse JSON SANS redirection
        return new JsonResponse([
            'status' => 'success',
            'locale' => $locale,
            'translations' => $translations
        ]);
    }

    private function getAllTranslations(string $locale): array
    {
        $translations = [];
        $catalogue = $this->translator->getCatalogue($locale);
        
        // Récupérer toutes les traductions du domaine 'messages'
        if ($messages = $catalogue->all('messages')) {
            foreach ($messages as $key => $translation) {
                $translations[$key] = $translation;
            }
        }
        
        return $translations;
    }
}