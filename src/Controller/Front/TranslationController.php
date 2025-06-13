<?php

// src/Controller/TranslationController.php
namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationController extends AbstractController
{
    public function translateBatch(Request $request, TranslatorInterface $translator): JsonResponse
    {
        $locale = $request->query->get('locale');
        $keys = json_decode($request->getContent(), true)['keys'] ?? [];
        
        $translations = [];
        foreach ($keys as $key) {
            $translations[$key] = $translator->trans($key, [], null, $locale);
        }
        
        return $this->json($translations);
    }
}