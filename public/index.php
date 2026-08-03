<?php

// PHP 8.4 signale des depreciations dans sensio/framework-extra-bundle,
// paquet abandonne que nous ne pouvons pas corriger. Affichees, elles
// ecrivent dans la sortie avant que la session ne pose ses en-tetes, ce
// qui casse l'application entiere. Elles restent journalisees.
ini_set('display_errors', '0');

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
